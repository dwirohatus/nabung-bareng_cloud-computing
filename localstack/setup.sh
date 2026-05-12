#!/bin/bash

echo "Setup LocalStack Started..."

# =========================
# CREATE S3 BUCKET
# =========================
awslocal s3 mb s3://bukti-transfer

echo "Bucket bukti-transfer berhasil dibuat"

# =========================
# CREATE SNS TOPIC
# =========================
awslocal sns create-topic \
--name reminder-topic

echo "SNS reminder-topic berhasil dibuat"

# =========================
# LIST BUCKET
# =========================
awslocal s3 ls

# =========================
# LIST SNS TOPIC
# =========================
awslocal sns list-topics

echo "Setup LocalStack Finished..."