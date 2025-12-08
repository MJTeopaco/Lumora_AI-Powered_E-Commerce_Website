# 1. Base Image: Use a stable Python image
FROM python:3.11-slim

# 2. Set Environment and Working Directory
ENV PYTHONUNBUFFERED=1
WORKDIR /app/Python

# 3. INSTALL SYSTEM DEPENDENCIES (THIS FIXES THE NUMPY CRASH)
# 'build-essential' is crucial for compiling C extensions like numpy
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        build-essential \
        libpq-dev \
        && \
    rm -rf /var/lib/apt/lists/*

# 4. Copy Application Code and Models
COPY . /app/Python

# 5. Install Python Dependencies
# Use --no-cache-dir to force a fresh, clean install
RUN pip install --upgrade pip && \
    pip install --no-cache-dir -r requirements.txt

# 6. Expose the Port and Define the Start Command
EXPOSE 8080
# This command is stable and uses a hardcoded port
CMD ["gunicorn", "app:app", "-b", "0.0.0.0:8080"]