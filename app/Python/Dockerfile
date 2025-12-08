# Dockerfile for lumora-ai-api
# Using bookworm (Debian 12) - latest stable release with active support
FROM python:3.11-bookworm

ENV PYTHONUNBUFFERED=1
WORKDIR /app/Python

# INSTALL SYSTEM DEPENDENCIES
# Added dependencies specifically for numpy/scipy compilation
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        build-essential \
        gfortran \
        libopenblas-dev \
        liblapack-dev \
        pkg-config \
        && \
    rm -rf /var/lib/apt/lists/*

# Copy requirements first for better Docker layer caching
COPY requirements.txt /app/Python/

# Install Python Dependencies
# Install numpy first separately to ensure it builds correctly
RUN pip install --upgrade pip && \
    pip install --no-cache-dir numpy && \
    pip install --no-cache-dir -r requirements.txt

# Copy Application Code and Models
COPY . /app/Python

# Define the Start Command
EXPOSE 8080
CMD ["gunicorn", "app:app", "-b", "0.0.0.0:8080", "--workers", "2", "--timeout", "120"]