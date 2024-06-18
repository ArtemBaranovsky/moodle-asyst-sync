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



def main():
    parser = argparse.ArgumentParser()

    # Where are we?
    location = ".";
    if getattr(sys, 'frozen', False):
        # running in a bundle
        location = sys._MEIPASS

    # Required parameters
    parser.add_argument(
        "--data",
        #default=None,
        default="/var/www/html/moodle/asyst/Source/Skript/outputs/test.tsv",
        type=str,
        # required=True,
        required=False,
        help="The input data file for the task.",
    )
    parser.add_argument(
        "--output_dir",
        # default=None,
        default="/var/www/html/moodle/asyst/Source/Skript/outputs",
        type=str,
        # required=True,
        required=False,
        help="The output directory where predictions will be written.",
    )
    parser.add_argument(
        "--model_dir",
        # default=None,
        default=location+"/Skript/german/models",
        type=str,
        # required=True,
        required=False,
        help="The directory where the ML models are stored.",
    )
    args = parser.parse_args()

    # open a log file next to the executable with line buffering
    # out = open("log.txt", "a",buffering=1);

    # print("Started German processing in",location,file=out);

    # import SentenceTransformer-model
    start_time = time.time()

    # print("Reading from",args.data, file=out);

    with open(os.path.join(location,args.data)) as ft:
        dft = pd.read_csv(ft, delimiter='\t')

    # Sentences we want sentence embeddings for
    sentences1_test = dft['referenceAnswer'].values.tolist()
    sentences2_test = dft['studentAnswer'].values.tolist()
    # print("Input read:",sentences2_test, file=out);

    # Use BERT for mapping tokens to embeddings
    word_embedding_model = models.Transformer('sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2')
    # pooling operation can choose by setting true (Apply mean pooling to get one fixed sized sentence vector)
    pooling_model = models.Pooling(word_embedding_model.get_word_embedding_dimension(),
                                   pooling_mode_mean_tokens=True,
                                   pooling_mode_cls_token=False,
                                   pooling_mode_max_tokens=False)

    # compute the sentence embeddings for both sentences
    model = SentenceTransformer(modules=[word_embedding_model, pooling_model])
    # print("Model loaded", file=out);

    sentence_embeddings1_test = model.encode(sentences1_test, convert_to_tensor=True, show_progress_bar=False)
    # print("Embeddings RefA:",sentence_embeddings1_test,file=out);

    sentence_embeddings2_test = model.encode(sentences2_test, convert_to_tensor=True, show_progress_bar=False)
    # print("Embeddings found", file=out);

    # Possible concatenations from the embedded sentences can be selected
    def similarity(sentence_embeddings1, sentence_embeddings2):
        # I2=(|u − v| + u ∗ v)
        simi = abs(np.subtract(sentence_embeddings1, sentence_embeddings2)) + np.multiply(sentence_embeddings1,
                                                                                          sentence_embeddings2)

        return simi

    # calls the similarity function and get the concatenated values between the sentence embeddings
    computed_simis_test = similarity(sentence_embeddings1_test, sentence_embeddings2_test)

    # get the sentence embeddings and the labels fpr train and test

    X_test = computed_simis_test
    # Y_test = np.array(dft['label'])

    # UP: read pre-trained LR model
    clf_log = pickle.load(open("/var/www/html/moodle/asyst/Source/Skript/german/models/clf_BERT.pickle", "rb"))


    # print('--------Evaluate on Testset------- ', file=out)
    predictions = clf_log.predict(X_test)

    # UP print results
    with open(args.output_dir + "/predictions.txt", "w") as writer:
        writer.write("question\treferenceAnswer\tstudentAnswer\tsuggested grade\tobserved grade\n")
        for i in range(len(dft)):
            hrpred = "incorrect"
            if predictions[i] == 1:
                hrpred = "correct"
            writer.write(
                str(dft.iloc[i][0])
                + "\t"
                + str(dft.iloc[i][1])
                + "\t"
                + str(dft.iloc[i][2])
                + "\t"
                + str(hrpred)
                + "\t"
                + str(dft.iloc[i][3])
                + "\n"
            )

    # print('\nExecution time:', time.strftime("%H:%M:%S", time.gmtime(time.time() - start_time)),file=out)


if __name__ == "__main__":
    main()
