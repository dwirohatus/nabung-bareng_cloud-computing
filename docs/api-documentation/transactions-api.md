# Transactions API

## Deposit

Endpoint:
POST /api/transactions/deposit.php

Request:

{
"goal_id": 1,
"amount": 200000
}

Response:

{
"status": true,
"message": "Setoran berhasil"
}

---

## Withdraw

Endpoint:
POST /api/transactions/withdraw.php

Response:

{
"status": true,
"message": "Withdrawal berhasil"
}
