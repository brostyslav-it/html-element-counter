SELECT SUM(count) AS total
FROM request
WHERE element_id = ?
  AND domain_id = ?;