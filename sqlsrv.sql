/****** Script for SelectTopNRows command from SSMS  ******/
SELECT a.[TABLE_SCHEMA] AS "schema"
      ,a.[TABLE_NAME] AS "table"
      ,a.[COLUMN_NAME] AS "column"
	  ,b.[is_identity] AS "identity"
      ,a.[COLUMN_DEFAULT] AS "default"
	  ,b.[is_nullable] AS "nullable"
      ,a.[DATA_TYPE] AS "type"
      ,a.[CHARACTER_MAXIMUM_LENGTH] AS "length"
  FROM [fenix].[INFORMATION_SCHEMA].[COLUMNS] AS a, (
	  SELECT TOP (1000) b.[name] AS "schema", a.[name] AS "table", c.[name] AS "column",
	  c.[max_length], c.[is_nullable], c.[is_identity]
	  FROM [fenix].[sys].[tables] AS a
	  JOIN [fenix].[sys].[schemas] AS b ON a.schema_id = b.schema_id
	  JOIN [fenix].[sys].[columns] AS c ON a.object_id = c.object_id
	  ORDER BY "schema" ASC, "table" ASC, c.[column_id] ASC 
  ) AS b
  WHERE (a.[TABLE_SCHEMA] = b."schema" AND a.[TABLE_NAME] = b."table" AND a.[COLUMN_NAME] = b."column")
  ORDER BY a.[TABLE_SCHEMA] ASC, a.[TABLE_NAME] ASC, a.[ORDINAL_POSITION] ASC


/* para saber las llaves primarias de una tabla */
SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE OBJECTPROPERTY(OBJECT_ID(CONSTRAINT_SCHEMA + '.' + QUOTENAME(CONSTRAINT_NAME)), 'IsPrimaryKey') = 1
AND TABLE_NAME = 'usuario_rol' AND TABLE_SCHEMA = 'seguridad'

