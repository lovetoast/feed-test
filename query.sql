SELECT b.id,
       c.name as 'Company name',
       b.name as 'Branch name',
       IF(Date_format(s.visit_datetime, '%m-%y') IS NOT NULL,
       Date_format(s.visit_datetime, '%m-%y'), 'N/A') AS 'Month-Year',
       SUM(CASE
             WHEN s.social_type_id = 1 THEN 1
             ELSE 0
           END)                                       AS 'Tripadvisor total',
       Avg(CASE
             WHEN s.social_type_id = 1 THEN social_raw_score
             ELSE NULL
           END)                                       AS 'Tripadvisor average',
       SUM(CASE
             WHEN s.social_type_id = 2 THEN 1
             ELSE 0
           END)                                       AS 'Facebook total',
       Avg(CASE
             WHEN s.social_type_id = 2 THEN social_raw_score
             ELSE NULL
           END)                                       AS 'Facebook average',
       SUM(CASE
             WHEN s.social_type_id = 3 THEN 1
             ELSE 0
           END)                                       AS 'Google total',
       Avg(CASE
             WHEN s.social_type_id = 3 THEN social_raw_score
             ELSE NULL
           END)                                       AS 'Google average',
        COUNT(*)                                       AS 'Total',
       Avg(social_raw_score)                           AS 'Total average'
FROM   survey_responses s
       right outer join branches b
                     ON s.branch_id = b.id
                        AND social_raw_score IS NOT NULL AND s.survey_mode_id = 8 AND s.status IN (1,2,5)
       join companies c
         ON c.id = b.company_id AND c.status =1 #
WHERE b.status = 1 AND brand_site = 0
GROUP  BY branch_id,
          Date_format(s.visit_datetime, '%m-%y')
          ORDER BY branch_id ASC,
          Date_format(s.visit_datetime, '%m-%y') ASC;

/*

Left outer joins are used to ensure all branches are retrieved.

We put the queries in the join to ensure integrity of the outer joins.

If we added a where query it would eliminate outer joins.

*/