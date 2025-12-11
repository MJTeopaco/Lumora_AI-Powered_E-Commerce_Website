# Dockerfile for lumora-ai-api
# Base Image: Uses the latest stable patch version of Python 3.11 
# (e.g., 3.11.x on Debian Bookworm)
FROM python:3.11-bookworm

# Set environment variables
ENV PYTHONUNBUFFERED=1
WORKDIR /app/Python

# --- INSTALL SYSTEM DEPENDENCIES ---
# Install libraries required to compile numpy, scipy, and scikit-learn from source
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        build-essential \
        gfortran \
        libopenblas-dev \
        liblapack-dev \
        pkg-config \
        && \
    rm -rf /var/lib/apt/lists/*

# Copy requirements file first for effective Docker layer caching
COPY requirements.txt /app/Python/

# --- INSTALL PYTHON DEPENDENCIES ---
# Install Python packages using the uploaded requirements.txt
# We install numpy separately first to aid in correct compilation, then the rest.
RUN pip install --upgrade pip && \
    pip install --no-cache-dir numpy && \
    pip install --no-cache-dir -r requirements.txt

# Copy the rest of the application code and model assets
COPY . /app/Python

# Define the Start Command
# The ML API will be served via gunicorn on port 8080
EXPOSE 8080
CMD ["gunicorn", "app:app", "-b", "0.0.0.0:8080", "--workers", "2", "--timeout", "120"]