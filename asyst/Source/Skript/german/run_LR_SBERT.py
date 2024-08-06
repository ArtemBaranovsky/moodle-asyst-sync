import os
import sys
import time
import numpy as np
import pandas as pd

# UP
import pickle
import argparse

from sklearn import metrics
from sentence_transformers import models, SentenceTransformer
from sklearn.linear_model import LogisticRegression, Perceptron
from sklearn.metrics import confusion_matrix
from sklearn.model_selection import cross_validate, cross_val_predict

__author__ = "Yunus Eryilmaz"
__version__ = "1.0"
__date__ = "21.07.2021"
__source__ = "https://pypi.org/project/sentence-transformers/0.3.0/"



def process_data(data):
    parser = argparse.ArgumentParser()

    parser.add_argument(
        "--model_dir",
        # default=None,
        default="/var/www/html/moodle/asyst/Source/Skript/german/models",
        type=str,
        required=False,
        help="The directory where the ML models are stored.",
    )

    args = parser.parse_args()

    referenceAnswer = data['referenceAnswer']
    studentAnswers = data['studentAnswers']

    # Use BERT for mapping tokens to embeddings
    word_embedding_model = models.Transformer('sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2')
    # pooling operation can choose by setting true (Apply mean pooling to get one fixed sized sentence vector)
    pooling_model = models.Pooling(word_embedding_model.get_word_embedding_dimension(),
                                   pooling_mode_mean_tokens=True,
                                   pooling_mode_cls_token=False,
                                   pooling_mode_max_tokens=False)

    # compute the sentence embeddings for both sentences
    model = SentenceTransformer(modules=[word_embedding_model, pooling_model])

    sentence_embeddings1 = model.encode([referenceAnswer] * len(studentAnswers), convert_to_tensor=True, show_progress_bar=False)
    sentence_embeddings2 = model.encode(studentAnswers, convert_to_tensor=True, show_progress_bar=False)

    computed_simis_test = similarity(sentence_embeddings1, sentence_embeddings2)
    X_test = computed_simis_test

    # UP: read pre-trained LR model
    clf_log = pickle.load(open("/var/www/html/moodle/asyst/Source/Skript/german/models/clf_BERT.pickle", "rb"))
    predictions = clf_log.predict(X_test)


    results = []
    for i in range(len(studentAnswers)):
        result = {
            "predicted_grade": "correct" if predictions[i] == 1 else "incorrect"
        }
        results.append(result)

    return results

# Possible concatenations from the embedded sentences can be selected
def similarity(sentence_embeddings1, sentence_embeddings2):
    # I2=(|u − v| + u ∗ v)
    simi = abs(np.subtract(sentence_embeddings1, sentence_embeddings2)) + np.multiply(sentence_embeddings1,
                                                                                      sentence_embeddings2)

    return simi