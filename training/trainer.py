import os
import torch
from tqdm import tqdm
from torch.optim import AdamW
from torch.utils.data import DataLoader, Dataset
from sklearn.metrics import accuracy_score
from transformers import AutoTokenizer, get_linear_schedule_with_warmup

from utils import AverageMeter


class Trainer:
    def __init__(
        self,
        dataloader_workers: int,
        device: str,
        epochs: int,
        learning_rate: float,
        model: torch.nn.Module,
        tokenizer: AutoTokenizer,
        save_dir: str,
        train_batch_size: int,
        train_set: Dataset,
        valid_batch_size: int,
        valid_set: Dataset,
        evaluate_on_accuracy: bool = False
    ) -> None:
        self.device = device
        self.epochs = epochs
        self.save_dir = save_dir
        self.train_batch_size = train_batch_size
        self.valid_batch_size = valid_batch_size
        self.train_loader = DataLoader(
            train_set,
            batch_size=train_batch_size,
            num_workers=dataloader_workers,
            shuffle=True
        )
        self.valid_loader = DataLoader(
            valid_set,
            batch_size=train_batch_size,
            num_workers=dataloader_workers,
            shuffle=False
        )
        self.tokenizer = tokenizer
        self.model = model.to(self.device)
        self.optimizer = AdamW(self.model.parameters(), lr=learning_rate)

        num_training_steps = len(self.train_loader) * epochs
        num_warmup_steps = int(0.1 * num_training_steps)
        self.scheduler = get_linear_schedule_with_warmup(
            self.optimizer,
            num_warmup_steps=num_warmup_steps,
            num_training_steps=num_training_steps
        )

        self.train_loss = AverageMeter()
        self.evaluate_on_accuracy = evaluate_on_accuracy
        if evaluate_on_accuracy:
            self.best_valid_score = 0
        else:
            self.best_valid_score = float("inf")

        self.log_file = os.path.join("question_generator", "training", "log", "train_log.txt")
        os.makedirs(os.path.dirname(self.log_file), exist_ok=True)
        with open(self.log_file, "w") as f:
            f.write("Epoch\tTrain Loss\tLearning Rate\tValidation Loss\n")

    def train(self) -> None:
        for epoch in range(1, self.epochs + 1):
            self.model.train()
            self.train_loss.reset()

            with tqdm(total=len(self.train_loader), unit="batches") as tepoch:
                tepoch.set_description(f"epoch {epoch}")
                for data in self.train_loader:
                    self.optimizer.zero_grad()
                    data = {key: value.to(self.device) for key, value in data.items()}
                    output = self.model(**data)
                    loss = output.loss
                    loss.backward()
                    self.optimizer.step()
                    self.scheduler.step()
                    self.train_loss.update(loss.item(), self.train_batch_size)
                    tepoch.set_postfix({
                        "train_loss": self.train_loss.avg,
                        "lr": self.scheduler.get_last_lr()[0]
                    })
                    tepoch.update(1)

            if self.evaluate_on_accuracy:
                valid_score = self.evaluate_accuracy(self.valid_loader)
                improved = valid_score > self.best_valid_score
            else:
                valid_score = self.evaluate(self.valid_loader)
                improved = valid_score < self.best_valid_score

            log_str = f"{epoch}\t{self.train_loss.avg:.2f}\t{self.scheduler.get_last_lr()[0]:.2e}\t{valid_score:.2f}"
            self.log(log_str)

            if improved:
                self.log(f"Validation score improved. Saving model to {self.save_dir}.")
                self.best_valid_score = valid_score
                self._save()

    def log(self, message: str) -> None:
        with open(self.log_file, "a") as f:
            f.write(message + "\n")

    @torch.no_grad()
    def evaluate(self, dataloader: DataLoader) -> float:
        self.model.eval()
        eval_loss = AverageMeter()
        with tqdm(total=len(dataloader), unit="batches") as tepoch:
            tepoch.set_description("validation")
            for data in dataloader:
                data = {key: value.to(self.device) for key, value in data.items()}
                output = self.model(**data)
                loss = output.loss
                eval_loss.update(loss.item(), self.valid_batch_size)
                tepoch.set_postfix({"valid_loss": eval_loss.avg})
                tepoch.update(1)
        return eval_loss.avg

    @torch.no_grad()
    def evaluate_accuracy(self, dataloader: DataLoader) -> float:
        self.model.eval()
        accuracy = AverageMeter()
        with tqdm(total=len(dataloader), unit="batches") as tepoch:
            tepoch.set_description("validation")
            for data in dataloader:
                data = {key: value.to(self.device) for key, value in data.items()}
                output = self.model(**data)
                preds = torch.argmax(output.logits, dim=1)
                score = accuracy_score(data["labels"].cpu(), preds.cpu())
                accuracy.update(score, self.valid_batch_size)
                tepoch.set_postfix({"valid_acc": accuracy.avg})
                tepoch.update(1)
        return accuracy.avg

    def _save(self) -> None:
        self.tokenizer.save_pretrained(self.save_dir)
        self.model.save_pretrained(self.save_dir)