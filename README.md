# Nettbureau Integration Project
Creates (or reuses) an **Organization → Person → Lead** in the Pipedrive API using a JSON test file.  

## Prerequisites

- **PHP 8+**
- Internet access
- A Pipedrive API token with access to the **nettbureaucase** domain

## Setup

1) **Clone**
   ```bash
   git clone https://github.com/andychri/integration_project
   cd integration_project
   ```

2) **API token**  
   Create a `.env` file in the project root:
   ```
   PIPEDRIVE_API_TOKEN=**Token**
   ```

3) **Test data**  
   Some sample files that used for testing

## Run

### Choose a specific test data file 
php src/run.php tests/test_data.json

or

php src/run.php tests/test_data2.json

### Output

- `PASS` if run #1 and run #2 returned the **same** org/person/lead IDs.  
- `FAIL` otherwise.  
- Raw API JSON for (Organization, Person, Lead) is printed for review.

> **Note:** There’s a `sleep(2)` between runs to allow Pipedrive’s search index to catch up (eventual consistency).

## What it does

- **Organization (v2)**
  - Searches exact name: `"[{contact_type}] {name}"`.
  - If not found, creates it.
- **Person (v2)**
  - Searches exact `name`.
  - If not found, creates it with optional `emails[]`, `phones[]`.
  - Sets custom field for contact type (maps `Privat/Borettslag/Bedrift` → `27/28/29`).
- **Lead**
  - Searches (v2) by exact `title = "[LEAD] {name}"`.
  - If not found, **creates via v1** (Pipedrive quirk) and links to person + org.
  - Optional custom fields: housing type, property size, deal type, comment.

## File overview

```
src/
  run.php
  pipedrive_lead_integration.php  
  helpers.php                     
tests/
  test_data1.json 
  test_data2.json
  test_data3.json
  test_data4.json
  test_data5.json
  test_data.json6
```

## Error handling

- HTTP helpers return **`null`** on failure (timeout/JSON decode issues).
- Each `create*` function checks for `null` and returns `null` early.
- 
## Issues

- **Sometimes fails on the first run**  
  Keep the `sleep(2)` between runs to let the search index catch up.

- **“Missing PIPEDRIVE_API_TOKEN”**  
  Ensure `.env` exists and the token is set.
