SELECT AVG(duration) AS average_duration
FROM request
WHERE domain_id = ?
  AND TIMESTAMPDIFF(HOUR, time, NOW()) < ?;
