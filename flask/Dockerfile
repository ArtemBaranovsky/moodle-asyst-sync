FROM python:3.11-slim

# Installing system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    python3-venv \
    && rm -rf /var/lib/apt/lists/*

# Creating and activating the virtual environment \
RUN python3 -m venv /opt/myenv
ENV PATH="/opt/myenv/bin:$PATH"

WORKDIR /app
COPY . /app
COPY ./asyst /app/asyst

# Installing dependencies
RUN /opt/myenv/bin/pip install --no-cache-dir -i https://pypi.tuna.tsinghua.edu.cn/simple -r /app/requirements.txt
RUN /opt/myenv/bin/python3 -m pip install --upgrade setuptools wheel


# Set permissions
RUN chown -R www-data:www-data /app/asyst
RUN chmod -R 755 /app/asyst

# Open port for Flask API
EXPOSE 5000

# Launch application
CMD ["python", "/app/api.py"]