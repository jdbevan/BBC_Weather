SELECT *
FROM forecasts on_the_day
JOIN forecasts three_days_before
    ON three_days_before.days_before = 2
    AND on_the_day.location_id = three_days_before.location_id
    AND three_days_before.forecast_day = on_the_day.forecast_day
    AND TIME(three_days_before.datetime_recorded) = TIME(on_the_day.datetime_recorded)
JOIN locations ON on_the_day.location_id = locations.id
WHERE on_the_day.days_before = 0
AND on_the_day.forecast_day = '2012-05-10'
AND locations.id = 1







SELECT locations.name, on_the_day.forecast_day, on_the_day.maximum_temp_celsius as ontheday, three_days_before.maximum_temp_celsius as threebefore
FROM forecasts on_the_day
JOIN forecasts three_days_before
    ON three_days_before.days_before = 2
    AND on_the_day.location_id = three_days_before.location_id
    AND three_days_before.forecast_day = on_the_day.forecast_day
    AND TIME(three_days_before.datetime_recorded) = TIME(on_the_day.datetime_recorded)
JOIN locations ON on_the_day.location_id = locations.id
WHERE on_the_day.days_before = 0
AND on_the_day.maximum_temp_celsius IS NOT NULL
