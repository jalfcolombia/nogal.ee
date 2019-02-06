SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE OBJECTPROPERTY(OBJECT_ID(CONSTRAINT_SCHEMA + '.' + QUOTENAME(CONSTRAINT_NAME)), 'IsPrimaryKey') = 1
AND TABLE_NAME = 'aprendiz' AND TABLE_SCHEMA = 'general'

SELECT COL_NAME(fc.parent_object_id, fc.parent_column_id) AS constraint_column_name,
	OBJECT_NAME (f.referenced_object_id) AS referenced_object,
	COL_NAME(fc.referenced_object_id, fc.referenced_column_id) AS referenced_column_name
FROM sys.foreign_keys AS f  
INNER JOIN sys.foreign_key_columns AS fc ON f.object_id = fc.constraint_object_id   
WHERE f.parent_object_id = OBJECT_ID('general.ficha_aprendiz')
AND is_disabled = 0