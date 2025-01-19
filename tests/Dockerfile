FROM python:3.10-slim

RUN apt-get update && apt-get install -y \
    wget \
    unzip \
    curl \
    xvfb \
    chromium \
    chromium-driver \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt /app/requirements.txt
WORKDIR /app
RUN pip install --no-cache-dir -r requirements.txt

COPY . /app

ENV PATH="/usr/lib/chromium:/usr/local/bin:$PATH"

CMD ["python", "main.py"]
