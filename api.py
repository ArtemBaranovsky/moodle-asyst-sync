# api.py

from flask import Flask, jsonify

app = Flask(__name__)

# Example of an endpoint returning JSON data
@app.route('/api/data', methods=['GET'])
def get_data():
# TODO: use run_LR_SBERT.py ASYST script instead
    data = {
        'message': 'Hello from Python API!',
        'data': {
            'key1': 'value1',
            'key2': 'value2'
        }
    }
    return jsonify(data)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)