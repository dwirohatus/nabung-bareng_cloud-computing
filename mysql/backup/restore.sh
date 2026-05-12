#!/bin/bash

echo "Restore Database Nabung Bareng"

mysql -u root -p nabung_bareng < ../init.sql

echo "Restore selesai"