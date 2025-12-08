# Dockerfile for lumora-ai-api
# CHANGED BASE IMAGE: 'buster' provides a more complete OS environment
FROM python:3.11-buster

ENV PYTHONUNBUFFERED=1
WORKDIR /app/Python

# INSTALL SYSTEM DEPENDENCIES (Needed for numpy and others)
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        build-essential \
        libpq-dev \
        && \
    rm -rf /var/lib/apt/lists/*

# Copy Application Code and Models
COPY . /app/Python

# Install Python Dependencies (Using clean install flag)
RUN pip install --upgrade pip && \
    pip install --no-cache-dir -r requirements.txt

# Define the Start Command
EXPOSE 8080
CMD ["gunicorn", "app:app", "-b", "0.0.0.0:8080"]