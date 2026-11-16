-- Tabla de asignación Indicador Estratégico ↔ Dirección Administrativa
-- Ejecutar en SQL Server sobre la base de Planificación.

IF OBJECT_ID('dbo.ProgramacionIndicadorDa', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.ProgramacionIndicadorDa
    (
        IdProgramcion uniqueidentifier NOT NULL CONSTRAINT DF_ProgramacionIndicadorDa_Id DEFAULT NEWSEQUENTIALID(),
        IdIndicador   uniqueidentifier NOT NULL,
        IdDa          uniqueidentifier NOT NULL,
        CONSTRAINT PK_ProgramacionIndicadorDa PRIMARY KEY (IdProgramcion),
        CONSTRAINT UQ_ProgramacionIndicadorDa_IndicadorDa UNIQUE (IdIndicador, IdDa),
        CONSTRAINT FK_ProgramacionIndicadorDa_Indicador
            FOREIGN KEY (IdIndicador) REFERENCES dbo.IndicadoresEstrategicos (IdIndicador),
        CONSTRAINT FK_ProgramacionIndicadorDa_Da
            FOREIGN KEY (IdDa) REFERENCES dbo.Das (IdDa)
    );
END
ELSE
BEGIN
    IF COL_LENGTH('dbo.ProgramacionIndicadorDa', 'IdIndicador') IS NULL
    BEGIN
        ALTER TABLE dbo.ProgramacionIndicadorDa
            ADD IdIndicador uniqueidentifier NULL;

        -- Ajustar datos existentes antes de forzar NOT NULL si aplica.
        ALTER TABLE dbo.ProgramacionIndicadorDa
            ALTER COLUMN IdIndicador uniqueidentifier NOT NULL;

        IF NOT EXISTS (
            SELECT 1 FROM sys.foreign_keys
            WHERE name = 'FK_ProgramacionIndicadorDa_Indicador'
        )
        BEGIN
            ALTER TABLE dbo.ProgramacionIndicadorDa
                ADD CONSTRAINT FK_ProgramacionIndicadorDa_Indicador
                    FOREIGN KEY (IdIndicador) REFERENCES dbo.IndicadoresEstrategicos (IdIndicador);
        END
    END

    IF NOT EXISTS (
        SELECT 1 FROM sys.indexes
        WHERE name = 'UQ_ProgramacionIndicadorDa_IndicadorDa'
          AND object_id = OBJECT_ID('dbo.ProgramacionIndicadorDa')
    )
    BEGIN
        ALTER TABLE dbo.ProgramacionIndicadorDa
            ADD CONSTRAINT UQ_ProgramacionIndicadorDa_IndicadorDa UNIQUE (IdIndicador, IdDa);
    END
END
GO
