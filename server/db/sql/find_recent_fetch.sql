SELECT *
FROM request
WHERE url_id = ?
  AND element_id = ?
  AND TIMESTAMPDIFF(MINUTE, time, NOW()) < ?;