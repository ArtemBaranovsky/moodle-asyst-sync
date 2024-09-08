# api.py

from flask import Flask, jsonify, request
import os
import sys

# Adding a path of module to system path
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), 'asyst/Source/Skript/german')))

from run_LR_SBERT import process_data

app = Flask(__name__)

@app.route('/api/autograde', methods=['POST'])
def get_data():
    try:
        data = request.get_json()
        if not data:
            return jsonify({"error": "No data provided"}), 400

        results = process_data(data)
        return jsonify(results)
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)