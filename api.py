# api.py

from flask import Flask, jsonify

# TODO: Adding correct path to ASYST script run_LR_SBERT.py
# from run_LR_SBERT import main

# sys.path.append(os.path.join(os.path.dirname(__file__), 'asyst/Source/Skript/german'))

app = Flask(__name__)

@app.route('/api/data', methods=['GET'])
def get_data():
    # Using path to data and model
    data_path = '/var/www/html/moodle/asyst/Source/Skript/outputs/test.tsv'
    model_dir = '/var/www/html/moodle/asyst/Source/Skript/german/models'

    # Obtaining results from run_asyst function
#     results => run_asyst(data_path, model_dir)

    # Demo dummy API output
    results = {
        'message': 'Hello from Python API!',
        'data': {
            'key1': 'value1',
            'key2': 'value2'
        }
    }

    # Returning result in JSON format
    return jsonify(results)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)