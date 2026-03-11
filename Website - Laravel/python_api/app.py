import os
os.environ["TOKENIZERS_PARALLELISM"] = "false"

import logging
from typing import List, Dict

import nltk
import torch
from flask import Flask, request, jsonify
from flask_cors import CORS
from transformers import AutoTokenizer, AutoModelForSeq2SeqLM, BertTokenizer, BertModel
from bert_score import BERTScorer
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
import json
from tqdm import tqdm

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)

# Device configuration
if torch.cuda.is_available():
    DEVICE = torch.device("cuda")
elif torch.backends.mps.is_available():
    DEVICE = torch.device("mps")
else:
    DEVICE = torch.device("cpu")

logger.info(f"Using device: {DEVICE}")

# Download required NLTK data
try:
    nltk.data.find('tokenizers/punkt')
except LookupError:
    nltk.download('punkt')

class QuestionGenerator:
    def __init__(self, model_name: str, model_path: str = None):
        self.ANSWER_TOKEN = "<answer>"
        self.CONTEXT_TOKEN = "<context>"
        self.SEQ_LENGTH = 512
        self.model_name = model_name

        try:
            if model_path and os.path.exists(model_path):
                load_path = model_path
                logger.info(f"Loading tokenizer from local path: {load_path}")
                self.tokenizer = AutoTokenizer.from_pretrained(load_path, local_files_only=True, use_fast=True)

                logger.info(f"Loading model from local path: {load_path}")
                self.model = AutoModelForSeq2SeqLM.from_pretrained(load_path, local_files_only=True).to(DEVICE)
            else:
                raise FileNotFoundError(f"Model path not found or invalid: {model_path}")

            self.model.eval()

            if self.tokenizer.pad_token is None:
                self.tokenizer.pad_token = self.tokenizer.eos_token

            logger.info(f"Model {model_name} loaded successfully")

        except Exception as e:
            logger.error(f"Error loading model {model_name}: {str(e)}")
            raise

    def generate_from_sentences(self, context: str) -> List[Dict]:
        try:
            sentences = nltk.sent_tokenize(context)
            qa_pairs = []

            for i in range(0, len(sentences), 8):
                batch = sentences[i:i + 8]
                batch_input = [f"{self.ANSWER_TOKEN} {s} {self.CONTEXT_TOKEN} {context}" for s in batch]

                inputs = self.tokenizer(
                    batch_input,
                    padding=True,
                    truncation=True,
                    max_length=self.SEQ_LENGTH,
                    return_tensors="pt"
                ).to(DEVICE)

                with torch.no_grad():
                    outputs = self.model.generate(
                        **inputs,
                        max_length=64,
                        num_beams=1,
                        early_stopping=True,
                        pad_token_id=self.tokenizer.pad_token_id
                    )

                for output, sentence in zip(outputs, batch):
                    question = self.tokenizer.decode(output, skip_special_tokens=True)
                    qa_pairs.append({"question": question, "answer": sentence})

            return qa_pairs

        except Exception as e:
            logger.error(f"Error generating QA pairs: {str(e)}")
            raise

class WeightedAveraging:
    def __init__(self, device):
        self.device = device
        self.scorer = None
        self.tokenizer = None
        self.model = None
        self.qa_vector_dir = "qa_vectors"
        
        # Create directory if it doesn't exist
        os.makedirs(self.qa_vector_dir, exist_ok=True)
    
    def load_bertscorer(self):
        """Initialize BERTScorer if not already loaded"""
        if self.scorer is None:
            self.scorer = BERTScorer(
                model_type="indobenchmark/indobert-base-p1",
                num_layers=12,
                lang="id",
                rescale_with_baseline=False
            )
    
    def load_embedding_model(self):
        """Initialize BERT tokenizer and model if not already loaded"""
        if self.tokenizer is None or self.model is None:
            self.tokenizer = BertTokenizer.from_pretrained("bert-base-uncased")
            self.model = BertModel.from_pretrained("bert-base-uncased").to(self.device)
            self.model.eval()
    
    def save_vectors(self, data, filename):
        """Save vectors to JSON file"""
        try:
            path = os.path.join(self.qa_vector_dir, filename)
            with open(path, 'w', encoding='utf-8') as f:
                json.dump(data, f, ensure_ascii=False, indent=2)
            return True
        except Exception as e:
            logger.error(f"Error saving vectors: {str(e)}")
            return False

    def load_json(self, path):
        """Helper method to load JSON files"""
        try:
            with open(path, "r", encoding="utf-8") as f:
                return json.load(f)
        except Exception as e:
            logger.error(f"Error loading JSON from {path}: {str(e)}")
            return None

    def generate_questions_all_models(self, models, context):
        """Generate QA pairs from context using all three models separately"""
        try:
            if not models:
                return {'success': False, 'error': 'Models not loaded'}, 400

            if len(context.strip()) == 0:
                return {'success': False, 'error': 'Text cannot be empty'}, 400

            # Generate questions with all three models separately
            qa_pairs_pegasus = models['pegasus'].generate_from_sentences(context)
            qa_pairs_bert2bert = models['bert2bert'].generate_from_sentences(context)
            qa_pairs_t5 = models['t5'].generate_from_sentences(context)

            return {
                'success': True,
                'results': {
                    'qa_pairs_pegasus': qa_pairs_pegasus,
                    'qa_pairs_bert2bert': qa_pairs_bert2bert,
                    'qa_pairs_t5': qa_pairs_t5,
                    'counts': {
                        'pegasus': len(qa_pairs_pegasus),
                        'bert2bert': len(qa_pairs_bert2bert),
                        't5': len(qa_pairs_t5)
                    }
                }
            }

        except Exception as e:
            logger.error(f"Error in generate_questions_all_models: {str(e)}")
            return {'success': False, 'error': str(e)}, 500
    
    def evaluate_bertscore(self, context, qa_pairs):
        """Evaluate QA pairs using BERTScore"""
        try:
            self.load_bertscorer()

            if not isinstance(qa_pairs, list) or len(qa_pairs) == 0:
                return {'success': False, 'error': 'qa_pairs must be a non-empty list'}, 400

            encoded_ref = self.scorer._tokenizer(context, return_tensors="pt", truncation=True, max_length=512)
            reference = self.scorer._tokenizer.decode(encoded_ref["input_ids"][0], skip_special_tokens=True)

            results = []
            for idx, pair in enumerate(qa_pairs):
                question = pair.get("question", "")
                answer = pair.get("answer", "")
                candidate = f"{question} {answer}"

                try:
                    P, R, F1 = self.scorer.score([candidate], [reference])
                    result = {
                        "qa_index": idx + 1,
                        "question": question,
                        "answer": answer,
                        "precision": float(P[0]),
                        "recall": float(R[0]),
                        "f1_score": float(F1[0])
                    }
                except Exception as e:
                    result = {
                        "qa_index": idx + 1,
                        "question": question,
                        "answer": answer,
                        "precision": None,
                        "recall": None,
                        "f1_score": None,
                        "error": str(e)
                    }

                results.append(result)

            average_f1 = sum(r["f1_score"] for r in results if r["f1_score"] is not None) / len(results) if results else 0

            return {
                'success': True,
                'context': context,
                'bertscore_results': results,
                'average_f1': round(average_f1, 4)
            }

        except Exception as e:
            logger.error(f"Error in evaluate_bertscore: {str(e)}", exc_info=True)
            return {'success': False, 'error': str(e)}, 500
    
    def calculate_model_weights(self, bertscore_results_per_model):
        """Calculate weights for models based on their F1 scores"""
        weights = {}
        total_f1_score = 0

        # Calculate total F1 for all models
        for model_name, results in bertscore_results_per_model.items():
            if isinstance(results, dict) and 'bertscore_results' in results:
                model_f1 = sum(r["f1_score"] for r in results['bertscore_results'] if r["f1_score"] is not None)
            elif isinstance(results, list):
                model_f1 = sum(r["f1_score"] for r in results if r["f1_score"] is not None)
            else:
                model_f1 = 0
            
            weights[model_name] = model_f1
            total_f1_score += model_f1

        # Normalize to get weights
        for model_name in weights:
            weights[model_name] = weights[model_name] / total_f1_score if total_f1_score > 0 else 0.0

        return weights
    
    def get_cls_embedding(self, text):
        """Generate CLS token embedding for a given text."""
        if not text or not isinstance(text, str):
            return None
            
        try:
            inputs = self.tokenizer(text, return_tensors="pt", truncation=True, padding=True, max_length=128)
            inputs = {k: v.to(self.device) for k, v in inputs.items()}
            with torch.no_grad():
                outputs = self.model(**inputs)
            cls_embedding = outputs.last_hidden_state[:, 0, :]
            return cls_embedding.squeeze().cpu().tolist()
        except Exception as e:
            logger.warning(f"Error generating embedding: {str(e)}")
            return None
    
    def evaluate_qa_vectors(self, context, qa_pairs):
        """Compute QA vector embeddings (question & answer)"""
        try:
            self.load_embedding_model()

            if not isinstance(qa_pairs, list) or len(qa_pairs) == 0:
                return {'success': False, 'error': 'qa_pairs must be a non-empty list'}, 400

            results = []
            for idx, pair in enumerate(qa_pairs):
                question = pair.get("question", "")
                answer = pair.get("answer", "")

                q_vec = self.get_cls_embedding(question)
                a_vec = self.get_cls_embedding(answer)

                results.append({
                    "qa_index": idx + 1,
                    "question": question,
                    "answer": answer,
                    "q_vec": q_vec if q_vec is not None else "embedding_failed",
                    "a_vec": a_vec if a_vec is not None else "embedding_failed"
                })

            return {
                "success": True,
                "context": context,
                "qa_vectors": results,
                "count": len(results)
            }

        except Exception as e:
            logger.error(f"Error in evaluate_qa_vectors: {str(e)}", exc_info=True)
            return {'success': False, 'error': str(e)}, 500
    
    def compute_final_vectors(self, qa_vectors, weight, model_name='unknown'):
        """Compute final weighted vectors for QA pairs"""
        try:
            if not qa_vectors:
                return {'success': False, 'error': 'qa_vectors are required'}, 400

            results = []
            for idx, item in enumerate(qa_vectors):
                try:
                    if item.get("q_vec") == "embedding_failed" or item.get("a_vec") == "embedding_failed":
                        raise ValueError("Embedding generation failed for this QA pair")

                    q_vec = np.array(item["q_vec"])
                    a_vec = np.array(item["a_vec"])
                    final_vec = weight * (q_vec + a_vec)

                    results.append({
                        "qa_index": idx + 1,
                        "question": item.get("question", ""),
                        "answer": item.get("answer", ""),
                        "final_vector": final_vec.tolist()
                    })
                except Exception as e:
                    results.append({
                        "qa_index": idx + 1,
                        "question": item.get("question", ""),
                        "answer": item.get("answer", ""),
                        "error": str(e),
                        "final_vector": None
                    })

            return {
                "success": True,
                "model_used": model_name,
                "weight": weight,
                "final_vectors": results,
                "count": len(results)
            }

        except Exception as e:
            logger.error(f"Error in compute_final_vectors: {str(e)}", exc_info=True)
            return {'success': False, 'error': str(e)}, 500
    
    def process_final_vectors_for_all_models(self, qa_vectors_all_models, model_weights):
        """Process final vectors for all models with their respective weights"""
        final_vectors_all_models = {}

        for model_name, qa_vectors_result in qa_vectors_all_models.items():
            model_weight = model_weights.get(model_name, 0.0)
            
            # Extract qa_vectors from result if it's a dict response
            if isinstance(qa_vectors_result, dict) and 'qa_vectors' in qa_vectors_result:
                qa_vectors = qa_vectors_result['qa_vectors']
            else:
                qa_vectors = qa_vectors_result

            final_vectors = []
            for idx, item in enumerate(qa_vectors):
                try:
                    if item.get("q_vec") == "embedding_failed" or item.get("a_vec") == "embedding_failed":
                        raise ValueError("Embedding generation failed for this QA pair")

                    q_vec = np.array(item["q_vec"])
                    a_vec = np.array(item["a_vec"])
                    final_vec = model_weight * (q_vec + a_vec)

                    final_vectors.append({
                        "qa_index": idx + 1,
                        "question": item.get("question", ""),
                        "answer": item.get("answer", ""),
                        "final_vector": final_vec.tolist(),
                        "source_model": model_name
                    })
                except Exception as e:
                    final_vectors.append({
                        "qa_index": idx + 1,
                        "question": item.get("question", ""),
                        "answer": item.get("answer", ""),
                        "final_vector": None,
                        "error": str(e),
                        "source_model": model_name
                    })

            final_vectors_all_models[model_name] = final_vectors

        return final_vectors_all_models
    
    def get_context_vectors(self, contexts):
        """Generate context vectors with better error handling"""
        self.load_embedding_model()
        
        if not isinstance(contexts, list):
            contexts = [contexts]
            
        results = []
        
        for context in tqdm(contexts, desc="Embedding Contexts"):
            if not context or not isinstance(context, str):
                results.append({
                    "context": context,
                    "vector": None,
                    "error": "Invalid context text"
                })
                continue
                
            try:
                inputs = self.tokenizer(
                    context,
                    return_tensors="pt",
                    truncation=True,
                    padding='max_length',
                    max_length=512
                )
                inputs = {k: v.to(self.device) for k, v in inputs.items()}

                with torch.no_grad():
                    outputs = self.model(**inputs)

                cls_vector = outputs.last_hidden_state[:, 0, :]
                vector = cls_vector.squeeze().cpu().numpy().tolist()

                results.append({
                    "context": context,
                    "vector": vector
                })
            except Exception as e:
                logger.warning(f"Error embedding context: {str(e)}")
                results.append({
                    "context": context,
                    "vector": None,
                    "error": str(e)
                })
        
        return results
    
    def rank_qa_pairs(self, context_vectors, final_vectors_files):
        """Rank QA pairs based on similarity to context vectors"""
        try:
            # Validate inputs
            if not context_vectors or not final_vectors_files:
                return {'success': False, 'error': 'context_vectors and final_vectors_files are required'}, 400

            # Load final vectors from all models
            final_vectors_all_models = {}
            for model_name, file_path in final_vectors_files.items():
                try:
                    data = self.load_json(os.path.join(self.qa_vector_dir, file_path))
                    if data and 'final_vectors' in data and data['success']:
                        final_vectors_all_models[model_name] = data['final_vectors']
                except Exception as e:
                    logger.warning(f"Failed to load vectors for {model_name}: {str(e)}")
                    continue

            # Combine all QA from all models, filtering out invalid entries
            all_final_qa = []
            for model_name, model_vectors in final_vectors_all_models.items():
                for qa in model_vectors:
                    if qa.get('final_vector') is not None:
                        all_final_qa.append({
                            **qa,
                            'source_model': model_name
                        })

            if not all_final_qa:
                return {'success': False, 'error': 'No valid QA vectors found'}, 400

            ranked_qa = []

            for context in context_vectors:
                if context.get('vector') is None:
                    continue

                context_vector = np.array(context["vector"]).reshape(1, -1)
                
                for qa in all_final_qa:
                    try:
                        qa_vector = np.array(qa["final_vector"]).reshape(1, -1)
                        sim = cosine_similarity(qa_vector, context_vector)[0][0]
                        
                        ranked_qa.append({
                            "context": context["context"],
                            "question": qa["question"],
                            "answer": qa["answer"],
                            "source_model": qa.get("source_model"),
                            "similarity_to_context": float(sim)
                        })
                    except Exception as e:
                        logger.warning(f"Error calculating similarity for QA pair: {str(e)}")
                        continue

            if not ranked_qa:
                return {'success': False, 'error': 'No valid similarity calculations'}, 400

            # Get top-N QA pairs (N = number of contexts)
            valid_contexts = len([c for c in context_vectors if c.get('vector') is not None])
            ranked_qa_sorted = sorted(ranked_qa, key=lambda x: x["similarity_to_context"], reverse=True)
            top_ranked_qa = ranked_qa_sorted[:min(valid_contexts, len(ranked_qa_sorted))]

            return {
                "success": True,
                "top_ranked_qa": top_ranked_qa,
                "count": len(top_ranked_qa),
                "total_qa_considered": len(all_final_qa)
            }

        except Exception as e:
            logger.error(f"Error in rank_qa_pairs: {str(e)}", exc_info=True)
            return {'success': False, 'error': str(e)}, 500

    def generate_weighted_averaging(self, models, context):
        """Complete integrated pipeline for processing QA generation and ranking"""
        try:
            # Step 1: Generate QA pairs from all models
            logger.info("Step 1: Generating QA pairs from all models")
            qa_generation_result = self.generate_questions_all_models(models, context)
            if not qa_generation_result.get('success'):
                return qa_generation_result

            qa_results = qa_generation_result['results']

            # Step 2: Evaluate BERTScore for each model
            logger.info("Step 2: Evaluating BERTScore for each model")
            bertscore_results = {}
            for model_name in ['pegasus', 'bert2bert', 't5']:
                qa_pairs = qa_results[f'qa_pairs_{model_name}']
                bertscore_result = self.evaluate_bertscore(context, qa_pairs)
                if bertscore_result.get('success'):
                    bertscore_results[model_name] = bertscore_result
                    # Save BERTScore results
                    self.save_vectors(bertscore_result, f"bertscore_{model_name}.json")

            # Step 3: Calculate model weights
            logger.info("Step 3: Calculating model weights")
            model_weights = self.calculate_model_weights(bertscore_results)

            # Step 4: Generate QA vectors for each model
            logger.info("Step 4: Generating QA vectors for each model")
            qa_vectors_all_models = {}
            for model_name in ['pegasus', 'bert2bert', 't5']:
                qa_pairs = qa_results[f'qa_pairs_{model_name}']
                qa_vectors_result = self.evaluate_qa_vectors(context, qa_pairs)
                if qa_vectors_result.get('success'):
                    qa_vectors_all_models[model_name] = qa_vectors_result
                    # Save QA vectors
                    self.save_vectors(qa_vectors_result, f"qa_vectors_{model_name}.json")

            # Step 5: Process final vectors for all models
            logger.info("Step 5: Processing final vectors for all models")
            final_vectors_all_models = self.process_final_vectors_for_all_models(
                qa_vectors_all_models, model_weights
            )

            # Save final vectors for each model
            for model_name, final_vectors in final_vectors_all_models.items():
                final_vectors_data = {
                    "success": True,
                    "model_used": model_name,
                    "weight": model_weights.get(model_name, 0.0),
                    "final_vectors": final_vectors,
                    "count": len(final_vectors)
                }
                self.save_vectors(final_vectors_data, f"final_vectors_{model_name}.json")

            # Step 6: Generate context vectors
            logger.info("Step 6: Generating context vectors")
            context_vectors = self.get_context_vectors([context])

            # Step 7: Rank QA pairs
            logger.info("Step 7: Ranking QA pairs")
            final_vectors_files = {
                model_name: f"final_vectors_{model_name}.json" 
                for model_name in ['pegasus', 'bert2bert', 't5']
            }
            
            ranking_result = self.rank_qa_pairs(context_vectors, final_vectors_files)

            return {
                "success": True,
                "pipeline_results": {
                    "qa_generation": qa_generation_result,
                    "bertscore_results": bertscore_results,
                    "model_weights": model_weights,
                    "qa_vectors_count": {
                        model: len(vectors.get('qa_vectors', []))
                        for model, vectors in qa_vectors_all_models.items()
                    },
                    "final_vectors_count": {
                        model: len(vectors)
                        for model, vectors in final_vectors_all_models.items()
                    },
                    "ranking_result": ranking_result
                }
            }

        except Exception as e:
            logger.error(f"Error in complete pipeline: {str(e)}", exc_info=True)
            return {'success': False, 'error': str(e)}, 500

# Initialize models and weighted averaging
models = {}
weighted_averaging = WeightedAveraging(DEVICE)

model_configs = {
    'bert2bert': {
        'path': 'models/bert',
        'name': 'bert2bert'
    },
    'pegasus': {
        'path': 'models/pegasusx',
        'name': 'pegasusx'
    },
    't5': {
        'path': 'models/t5',
        'name': 't5'
    }
}

def load_models():
    """Load all models when the app starts"""
    global models
    for model_key, config in model_configs.items():
        try:
            logger.info(f"Loading {model_key} model...")
            models[model_key] = QuestionGenerator(config['name'], config['path'])
            logger.info(f"{model_key} model loaded successfully")
        except Exception as e:
            logger.error(f"Failed to load {model_key} model: {str(e)}")

API_PREFIX = "/api/question-generator"

@app.route(f"{API_PREFIX}/health", methods=["GET"])
def health_check():
    """Health check endpoint"""
    if not models:
        load_models()

    return jsonify({
        'success': True,
        'status': 'healthy',
        'models_loaded': list(models.keys()),
        'device': str(DEVICE)
    })

@app.route(f"{API_PREFIX}/models", methods=["GET"])
def get_models():
    """Get available models"""
    if not models:
        load_models()

    return jsonify({
        'success': True,
        'available_models': list(models.keys()),
        'models': [
            {
                'name': k,
                'description': f'{v["name"]} model for question generation'
            }
            for k, v in model_configs.items()
        ],
        'model_configs': {k: v['name'] for k, v in model_configs.items()}
    })

@app.route(f"{API_PREFIX}/weighted-averaging", methods=["POST"])
def weighted_averaging_endpoint():
    """Endpoint for weighted averaging pipeline"""
    try:
        if not models:
            load_models()
        
        data = request.get_json()
        
        if not data or 'text' not in data:
            return jsonify({'success': False, 'error': 'Text is required'}), 400
        
        context = data['text']
        
        if len(context.strip()) == 0:
            return jsonify({'success': False, 'error': 'Text cannot be empty'}), 400
        
        result = weighted_averaging.generate_weighted_averaging(models, context)
        
        if result.get('success'):
            # Extract the ranking result for consistent format
            ranking_result = result["pipeline_results"]["ranking_result"]
            
            # Handle both success and error cases in ranking_result
            if ranking_result.get('success'):
                top_ranked_qa = ranking_result.get('top_ranked_qa', [])
                total_qa_considered = ranking_result.get('total_qa_considered', 0)
                
                # Transform the ranked QA to match the standard format
                qa_pairs = []
                for qa in top_ranked_qa:
                    qa_pairs.append({
                        'question': qa['question'],
                        'answer': qa['answer'],
                        'similarity_score': qa['similarity_to_context'],
                        'source_model': qa.get('source_model', 'unknown'),
                        'context': qa.get('context', context)
                    })
                
                # Log the detailed results (optional)
                logger.info(json.dumps(ranking_result, indent=2, ensure_ascii=False))
                
                # Return format consistent with generate_questions
                return jsonify({
                    'success': True,
                    'model_used': 'weighted_averaging',
                    'qa_pairs': qa_pairs,
                    'count': len(qa_pairs),
                    'total_generated': total_qa_considered,
                    'model_weights': result["pipeline_results"]["model_weights"],
                    'pipeline_stats': {
                        'bertscore_results': len(result["pipeline_results"]["bertscore_results"]),
                        'models_used': list(result["pipeline_results"]["model_weights"].keys()),
                        'qa_vectors_count': result["pipeline_results"]["qa_vectors_count"],
                        'final_vectors_count': result["pipeline_results"]["final_vectors_count"]
                    }
                })
            else:
                # Ranking failed, but we might still have some results
                pipeline_results = result["pipeline_results"]
                
                # Try to extract QA pairs from generation results if available
                qa_generation = pipeline_results.get("qa_generation", {})
                if qa_generation.get('success'):
                    all_qa_pairs = []
                    qa_results = qa_generation.get('results', {})
                    
                    for model_name in ['pegasus', 'bert2bert', 't5']:
                        model_qa = qa_results.get(f'qa_pairs_{model_name}', [])
                        for qa in model_qa:
                            all_qa_pairs.append({
                                'question': qa.get('question', ''),
                                'answer': qa.get('answer', ''),
                                'similarity_score': None,
                                'source_model': model_name,
                                'context': context
                            })
                    
                    return jsonify({
                        'success': True,
                        'model_used': 'weighted_averaging',
                        'qa_pairs': all_qa_pairs,
                        'count': len(all_qa_pairs),
                        'total_generated': len(all_qa_pairs),
                        'model_weights': pipeline_results.get("model_weights", {}),
                        'pipeline_stats': {
                            'bertscore_results': len(pipeline_results.get("bertscore_results", {})),
                            'models_used': list(pipeline_results.get("model_weights", {}).keys()),
                            'ranking_error': ranking_result.get('error', 'Unknown ranking error')
                        }
                    })
                else:
                    return jsonify({
                        'success': False, 
                        'error': f"Pipeline failed at ranking stage: {ranking_result.get('error', 'Unknown error')}"
                    }), 500
        else:
            return jsonify(result), 500
    
    except Exception as e:
        logger.error(f"Error in weighted_averaging_endpoint: {str(e)}")
        return jsonify({'success': False, 'error': str(e)}), 500


@app.route(f"{API_PREFIX}/generate", methods=["POST"])
def generate_questions():
    """Generate QA pairs from context"""
    try:
        if not models:
            load_models()
        
        data = request.get_json()
        
        if not data or 'text' not in data:
            return jsonify({'success': False, 'error': 'Text is required'}), 400
        
        context = data['text']
        model_type = data.get('model', 'bert2bert')
        
        if model_type not in models:
            return jsonify({'success': False, 'error': f'Model {model_type} not available'}), 400
        
        if len(context.strip()) == 0:
            return jsonify({'success': False, 'error': 'Text cannot be empty'}), 400
        
        qa_pairs = models[model_type].generate_from_sentences(context)
        
        # Ensure consistent format for qa_pairs
        formatted_qa_pairs = []
        for qa in qa_pairs:
            formatted_qa_pairs.append({
                'question': qa.get('question', ''),
                'answer': qa.get('answer', ''),
                'similarity_score': None,  # Not applicable for single model generation
                'source_model': model_type,
                'context': context
            })
        
        return jsonify({
            'success': True,
            'model_used': model_type,
            'qa_pairs': formatted_qa_pairs,
            'count': len(formatted_qa_pairs),
            'total_generated': len(formatted_qa_pairs)
        })
    
    except Exception as e:
        logger.error(f"Error in generate_questions: {str(e)}")
        return jsonify({'success': False, 'error': str(e)}), 500

if __name__ == '__main__':
    load_models()
    app.run(host='0.0.0.0', port=5001, debug=False)