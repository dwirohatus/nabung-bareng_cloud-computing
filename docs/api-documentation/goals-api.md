# Goals API

## Create Goal

Endpoint:
POST /api/goals/create_goal.php

Request:

{
"title": "Liburan Bali",
"target_amount": 10000000,
"deadline": "2026-12-31"
}

Response:

{
"status": true,
"message": "Goals berhasil dibuat"
}

---

## Get Goals

Endpoint:
GET /api/goals/get_goals.php

Response:

{
"status": true,
"data": []
}
