import argparse
import datasets
import torch
import json
import gc
import pandas as pd
from sklearn.model_selection import train_test_split
from transformers import (
    EncoderDecoderConfig,
    EncoderDecoderModel,
    AutoTokenizer
)
from dataset import QGDataset
from trainer import Trainer

torch.multiprocessing.set_sharing_strategy('file_system')

def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()

    parser.add_argument("--dataset_path", type=str, default="/Users/alex/Downloads/Code Tugas Akhir/Dataset/indo_squad.json")
    parser.add_argument("--epochs", type=int, default=10)
    parser.add_argument("--learning_rate", type=float, default=5e-5)
    parser.add_argument("--max_length", type=int, default=512)

    parser.add_argument("--train_batch_size", type=int, default=1)
    parser.add_argument("--valid_batch_size", type=int, default=4)

    parser.add_argument("--qg_model", type=str, default="/Users/alex/Downloads/Code Tugas Akhir/bert2bert-question-answer-generator")
    parser.add_argument("--pad_mask_id", type=int, default=-100)
    parser.add_argument("--save_dir", type=str, default="/Users/alex/Downloads/Code Tugas Akhir/bert2bert-qa-generator")

    device = "cuda" if torch.cuda.is_available() else "cpu"
    parser.add_argument("--device", type=str, default=device)

    parser.add_argument("--dataloader_workers", type=int, default=0)

    args, _ = parser.parse_known_args()
    return args

def get_tokenizer(checkpoint: str):
    tokenizer = AutoTokenizer.from_pretrained(checkpoint)
    tokenizer.add_special_tokens({'additional_special_tokens': ['<answer>', '<context>', '<question>']})
    return tokenizer

def get_model(checkpoint: str, device: str, tokenizer) -> EncoderDecoderModel:
    config = EncoderDecoderConfig.from_pretrained(checkpoint)
    config.decoder.use_cache = False
    model = EncoderDecoderModel.from_pretrained(checkpoint, config=config)

    model.encoder.resize_token_embeddings(len(tokenizer))
    model.decoder.resize_token_embeddings(len(tokenizer))

    return model.to(device)

def load_dataset(
    dataset_path: str,
    dataset_type: str = "squad",
    sample_ratio: float = 0.1,
    test_size: float = 0.1,
    random_seed: int = 42
) -> datasets.DatasetDict:
    with open(dataset_path, "r", encoding="utf-8") as f:
        raw_data = json.load(f)

    data_list = []

    if dataset_type == "squad":
        for item in raw_data.values():
            answers = item["answer"]
            data_list.append({
                "context": item["context"],
                "question": item["question"],
                "answers": {"text": [answers]} if isinstance(answers, str) else {"text": answers}
            })

    elif dataset_type == "pisa":
        if "Lembar 1" not in raw_data:
            raise ValueError("Key 'Lembar 1' tidak ditemukan dalam dataset PISA.")
        for item in raw_data["Lembar 1"]:
            answers = item["answers"]
            data_list.append({
                "context": str(item["context"]),
                "question": str(item["question"]),
                "answers": {
                    "text": [str(answers)] if isinstance(answers, (str, int)) else list(map(str, answers))
                }
            })
    else:
        raise ValueError("dataset_type harus 'squad' atau 'pisa'")

    df = pd.DataFrame(data_list)
    print(f"Total data sebelum sampling: {len(df)}")
    df = df.sample(frac=sample_ratio, random_state=random_seed).reset_index(drop=True)
    print(f"Total data setelah sampling ({sample_ratio * 100}%): {len(df)}")

    train_df, valid_df = train_test_split(df, test_size=test_size, random_state=random_seed)

    return datasets.DatasetDict({
        "train": datasets.Dataset.from_pandas(train_df),
        "validation": datasets.Dataset.from_pandas(valid_df)
    })