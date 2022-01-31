(SELECT * FROM enertek_combuster.testdata where test_id =2000 and rpm BETWEEN 0 AND 5000) As Tim

JOIN (SELECT
--  CONCAT(z.expected, IF(z.got-1>z.expected, CONCAT(' to ',z.got-1), '')) AS missing
z.expected,(z.got-1) As lastone
FROM (
 SELECT
  @rownum:=@rownum+1 AS expected,
  IF(@rownum=L.testdata_id, 0, @rownum:=L.testdata_id) AS got
 FROM
  (SELECT @rownum:=0) AS a
  JOIN (SELECT * FROM enertek_combuster.testdata where test_id =2000 and rpm BETWEEN 0 AND 5000) As L
  ORDER BY L.testdata_id
 ) AS z
WHERE z.got!=0 limit 1) As limit_value;