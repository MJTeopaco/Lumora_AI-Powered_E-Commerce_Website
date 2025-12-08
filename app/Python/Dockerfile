# 1. Base Image: Use a lightweight Python image
# Python 3.11 is seen in your logs, so we'll use a stable, slim version.
FROM python:3.11-slim

# 2. Set Environment Variables
# Ensures stdout/stderr are immediately flushed and Python runs unbuffered.
ENV PYTHONUNBUFFERED=1
# Set the working directory to the Python application folder
WORKDIR /app/Python

# 3. Install System Dependencies (Crucial for numpy/scikit-learn build)
# Installing build-essential and the required libs to ensure robust numpy installation.
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        build-essential \
        libpq-dev \
        && \
    rm -rf /var/lib/apt/lists/*

# 4. Copy Application Code and Models
# Copy the entire Python application code and dependency list
COPY . /app/Python

# 5. Install Python Dependencies (The Fix for 'numpy._core' ModuleNotFoundError)
# Use --no-cache-dir and upgrade pip to prevent caching old/corrupted packages.
# requirements.txt contains: Flask, gunicorn, joblib, scikit-learn, numpy, pandas
RUN pip install --upgrade pip && \
    pip install --no-cache-dir -r requirements.txt

# 6. Expose the Port (Gunicorn default is 8000, but logs showed 8080)
EXPOSE 8080

# 7. Start the Gunicorn Web Server
# This is the command that runs your Flask application using Gunicorn.
# The previous logs show gunicorn was running app:app on port 8080.
CMD ["gunicorn", "app:app", "-b", "0.0.0.0:8080"]