CREATE FUNCTION dbo.ObtenerIPCliente()
    RETURNS VARCHAR(50)
AS
BEGIN
    DECLARE
@ip VARCHAR(50);
SELECT @ip = client_net_address
FROM sys.dm_exec_connections
WHERE session_id = @@SPID;
RETURN @ip;
END;

CREATE TABLE Usuarios
(
    CodigoUsuario     char(3) primary key,
    CodigoTrabajador  char(10) NULL,
    IdPersona         char(15)     not null,
    Login             varchar(20)  not null,
    Llave             char(32)     not null,
    Email             varchar(100) not null,
    Pwd               varchar(100) not null,
    Foto              varchar(100) not null,
    CodigoRol         char(3)      not null,
    CodigoEstado      char(1)      not null,
    FechaHoraRegistro datetime     not null default getdate(),

    foreign key (CodigoEstado) references Estados (CodigoEstado),
)
    go

create table PEIs
(
    IdPei             uniqueidentifier      DEFAULT NEWSEQUENTIALID() PRIMARY KEY,
    Descripcion       Varchar(500) not null,
    FechaAprobacion   Date         not null,
    GestionInicio     int          not null,
    GestionFin        int          not null,
    CodigoEstado      char(1)      not null,
    FechaHoraRegistro datetime     not null default getdate(),
    CodigoUsuario     char(3)      not null,

    constraint chk_GInicio check (GestionInicio > 2000),
    constraint chk_GFin check (GestionFin > 2001),
    constraint chk_Gestion check (GestionInicio < GestionFin),
    constraint chk_Descripcion check (Descripcion != ''
) ,

	foreign key (CodigoEstado) references Estados(CodigoEstado),
	foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
) go

CREATE TRIGGER trg_Insert_Peis
    ON Peis AFTER INSERT
AS
BEGIN
INSERT INTO Auditoria_Peis (IdPei, Operacion, Usuario, IPCliente, DatosDespues)
SELECT i.IdPei,
       'INSERT',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),
       CONCAT(
               '{Descripcion:"', i.Descripcion,
               '", FechaAprobacion:"', i.FechaAprobacion,
               '",GestionInicio:"', i.GestionInicio,
               '", GestionFin:"', i.GestionFin,
               '", Estado:"', i.CodigoEstado,
               '"}'
       )
FROM inserted i;
END;

go

CREATE TRIGGER trg_Update_Peis
    ON Peis
    AFTER
UPDATE
    AS
BEGIN
INSERT INTO Auditoria_Peis (IdPei, Operacion, Usuario, IPCliente, DatosAntes, DatosDespues)
SELECT d.IdPei,
       'UPDATE',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),
       CONCAT(
               '{Descripcion:"', d.Descripcion,
               '", FechaAprobacion:"', d.FechaAprobacion,
               '",GestionInicio:"', d.GestionInicio,
               '", GestionFin:"', d.GestionFin,
               '", Estado:"', d.CodigoEstado,
               '"}'
       ),
       CONCAT(
               '{Descripcion:"', i.Descripcion,
               '", FechaAprobacion:"', i.FechaAprobacion,
               '",GestionInicio:"', i.GestionInicio,
               '", GestionFin:"', i.GestionFin,
               '", Estado:"', i.CodigoEstado,
               '"}'
       )
FROM deleted d
         JOIN inserted i ON d.IdPei = i.idPei;
END;

go

CREATE TRIGGER trg_Delete_Peis
    ON Peis
    AFTER
DELETE
AS
BEGIN
INSERT INTO Auditoria_Peis (IdPei, Operacion, Usuario, IPCliente, DatosAntes)
SELECT d.IdPei,
       'DELETE',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),
       CONCAT(
               '{Descripcion:"', d.Descripcion,
               '",FechaAprobacion:"', d.FechaAprobacion,
               '",GestionInicio:"', d.GestionInicio,
               '",GestionFin:"', d.GestionFin,
               '",Estado:"', d.CodigoEstado,
               '"}'
       )
FROM deleted d;
END;

go

CREATE TABLE Auditoria_Peis
(
    IdAuditoria  INT IDENTITY(1,1) PRIMARY KEY,
    IdPei        UNIQUEIDENTIFIER,
    Operacion    VARCHAR(10),   -- 'INSERT', 'UPDATE', 'DELETE'
    Usuario      NVARCHAR(100), -- Usuario que realizó la acción
    IPCliente    VARCHAR(50),   -- Dirección IP del cliente
    Fecha        DATETIME DEFAULT GETDATE(),
    DatosAntes   NVARCHAR(MAX), -- JSON opcional con estado anterior
    DatosDespues NVARCHAR(MAX)  -- JSON opcional con estado nuevo
)
    go

create table AreasEstrategicas
(
    IdAreaEstrategica uniqueidentifier          DEFAULT NEWSEQUENTIALID() PRIMARY KEY,
    IdPei             UNIQUEIDENTIFIER NOT NULL,
    Codigo            int              not null,
    Descripcion       Varchar(500)     not null,
    CodigoEstado      char(1)          not null,
    FechaHoraRegistro datetime         not null default getdate(),
    CodigoUsuario     char(3)          not null,

    constraint chk_Codigo_Area_Estrategica check (Codigo > 0),
    constraint chk_Descripcion_Area_Estrategica check (Descripcion != ''
) ,

    foreign key (IdPei) references Peis(IdPei),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
) go

CREATE UNIQUE INDEX [UQ_Area_Estrategica_Pei]
    ON [dbo].AreasEstrategicas(Codigo,IdPei)
    WHERE ([CodigoEstado] = 'V');

go

create table PoliticasEstrategicas
(
    IdPoliticaEstrategica uniqueidentifier          DEFAULT NEWSEQUENTIALID() PRIMARY KEY,
    IdAreaEstrategica     UNIQUEIDENTIFIER NOT NULL,
    Codigo                int              not null,
    Descripcion           Varchar(500)     not null,
    CodigoEstado          char(1)          not null,
    FechaHoraRegistro     datetime         not null default getdate(),
    CodigoUsuario         char(3)          not null,

    constraint chk_Codigo_Politica_Estrategica check (Codigo != ''
) ,
    constraint chk_Descripcion_Politica_Estrategica check (Descripcion != ''),

    foreign key (IdAreaEstrategica) references AreasEstrategicas(IdAreaEstrategica),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
) go

CREATE UNIQUE INDEX [UQ_Politica_Estrategica_Area_Estrategica]
    ON [dbo].PoliticasEstrategicas(Codigo,IdAreaEstrategica)
    WHERE ([CodigoEstado] = 'V');

go

create table ObjetivosEstrategicos
(
    IdObjEstrategico      uniqueidentifier          DEFAULT NEWSEQUENTIALID() PRIMARY KEY,
    IdAreaEstrategica     UNIQUEIDENTIFIER not null,
    IdPoliticaEstrategica UNIQUEIDENTIFIER not null,
    Codigo                int              not null,
    Objetivo              varchar(500)     not null,
    Producto              varchar(500)     not null,
    Indicador_Descripcion varchar(500)     not null,
    Indicador_Formula     varchar(500)     not null,
    IdPei                 UNIQUEIDENTIFIER not null,
    CodigoEstado          char(1)          not null,
    FechaHoraRegistro     datetime         not null default getdate(),
    CodigoUsuario         char(3)          not null,

    constraint chk_Codigo_Objetivo_Estrategico check (Codigo > 0),
    constraint chk_Objetivo_Objetivo_Estrategico check (Objetivo != ''
) ,
    constraint chk_Resultado_Objetivo_Estrategico check (Producto != ''),
    constraint chk_Indicador_Descripcion_Objetivo_Estrategico check (Indicador_Descripcion != ''),
    constraint chk_Indicador_Formula_Objetivo_Estrategico check (Indicador_Formula != ''),

    foreign key (IdAreaEstrategica) references AreasEstrategicas(IdAreaEstrategica),
    foreign key (IdPoliticaEstrategica) references PoliticasEstrategicas(IdPoliticaEstrategica),
    foreign key (IdPei) references Peis(IdPei),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
) go

CREATE UNIQUE INDEX [UQ_Objetivo_Area_Politica]
    ON [dbo].ObjetivosEstrategicos(IdPei, IdAreaEstrategica, IdPoliticaEstrategica, Codigo)
    WHERE ([CodigoEstado] = 'V');

go

CREATE TRIGGER trg_Insert_ObjEstrategico
    ON ObjetivosEstrategicos AFTER INSERT
AS
BEGIN
INSERT INTO Auditoria_ObjEstrategico (IdObjEstrategico, Operacion, Usuario, IPCliente, DatosDespues)
SELECT i.IdObjEstrategico,
       'INSERT',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),
       CONCAT('{IdAreaEstrategica:"', i.IdAreaEstrategica,
              '", IdPoliticaEstrategica:"', i.IdPoliticaEstrategica,
              '",Codigo:"', i.Codigo,
              '", Objetivo:"', i.Objetivo,
              '", Producto:"', i.Producto,
              '", Ind_Descripcion:"', i.Indicador_Descripcion,
              '", Ind_Formula:"', i.Indicador_Formula,
              '", Estado:"', i.CodigoEstado,
              '"}')
FROM inserted i;
END;

go

CREATE TRIGGER trg_Update_ObjEstrategico
    ON ObjetivosEstrategicos
    AFTER
UPDATE
    AS
BEGIN
INSERT INTO Auditoria_ObjEstrategico (IdObjEstrategico, Operacion, Usuario, IPCliente, DatosAntes, DatosDespues)
SELECT d.IdObjEstrategico,
       'UPDATE',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),
       CONCAT('{IdAreaEstrategica:"', d.IdAreaEstrategica,
              '", IdPoliticaEstrategica:"', d.IdPoliticaEstrategica,
              '",Codigo:"', d.Codigo,
              '", Objetivo:"', d.Objetivo,
              '", Producto:"', d.Producto,
              '", Ind_Descripcion:"', d.Indicador_Descripcion,
              '", Ind_Formula:"', d.Indicador_Formula,
              '", Estado:"', d.CodigoEstado,
              '"}'),
       CONCAT('{IdAreaEstrategica:"', i.IdAreaEstrategica,
              '", IdPoliticaEstrategica:"', i.IdPoliticaEstrategica,
              '",Codigo:"', i.Codigo,
              '", Objetivo:"', i.Objetivo,
              '", Producto:"', i.Producto,
              '", Ind_Descripcion:"', i.Indicador_Descripcion,
              '", Ind_Formula:"', i.Indicador_Formula,
              '", Estado:"', i.CodigoEstado,
              '"}')
FROM deleted d
         JOIN inserted i ON d.IdObjEstrategico = i.IdObjEstrategico;
END;

go

CREATE TRIGGER trg_Delete_ObjEstrategico
    ON ObjetivosEstrategicos
    AFTER
DELETE
AS
BEGIN
INSERT INTO Auditoria_ObjEstrategico (IdObjEstrategico, Operacion, Usuario, IPCliente, DatosAntes)
SELECT d.IdObjEstrategico,
       'DELETE',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),
       CONCAT('{IdAreaEstrategica:"', d.IdAreaEstrategica,
              '", IdPoliticaEstrategica:"', d.IdPoliticaEstrategica,
              '",Codigo:"', d.Codigo,
              '", Objetivo:"', d.Objetivo,
              '", Producto:"', d.Producto,
              '", Ind_Descripcion:"', d.Indicador_Descripcion,
              '", Ind_Formula:"', d.Indicador_Formula,
              '", Estado:"', d.CodigoEstado,
              '"}')
FROM deleted d;
END;

go

CREATE TABLE Auditoria_ObjEstrategico
(
    IdAuditoria      INT IDENTITY(1,1) PRIMARY KEY,
    IdObjEstrategico UNIQUEIDENTIFIER,
    Operacion        VARCHAR(10),   -- 'INSERT', 'UPDATE', 'DELETE'
    Usuario          NVARCHAR(100), -- Usuario que realizó la acción
    IPCliente        VARCHAR(50),   -- Dirección IP del cliente
    Fecha            DATETIME DEFAULT GETDATE(),
    DatosAntes       NVARCHAR(MAX), -- JSON opcional con estado anterior
    DatosDespues     NVARCHAR(MAX)  -- JSON opcional con estado nuevo
)
    go

CREATE TABLE Indicadores
(
    IdIndicador          uniqueidentifier          DEFAULT NEWSEQUENTIALID() PRIMARY KEY,

    Meta                 int              NOT NULL,
    Descripcion          varchar(500)     NOT NULL,
    LineaBase            int              NOT NULL,

    IdTipoResultado      uniqueidentifier NOT NULL,
    IdCategoriaIndicador uniqueidentifier NOT NULL,
    IdUnidadIndicador    uniqueidentifier NOT NULL,

    CodigoEstado         char(1)          NOT NULL,
    FechaHoraRegistro    datetime         NOT NULL DEFAULT GETDATE(),
    CodigoUsuario        char(3)          NOT NULL,

    CONSTRAINT chk_Codigo_Indicador CHECK (Meta >= 0),
    CONSTRAINT chk_LineaBase_Indicador CHECK (LineaBase >= 0),
    CONSTRAINT chk_Meta_Indicador CHECK (Meta >= 0),
    CONSTRAINT chk_Descripcion_Indicador CHECK (Descripcion <> ''),

    FOREIGN KEY (IdTipoResultado) REFERENCES CatTiposResultados (IdTipoResultado),
    FOREIGN KEY (IdCategoriaIndicador) REFERENCES CatCategoriasIndicadores (IdCategoriaIndicador),
    FOREIGN KEY (IdUnidadIndicador) REFERENCES CatUnidadesIndicadores (IdUnidadIndicador),
    FOREIGN KEY (CodigoEstado) REFERENCES Estados (CodigoEstado),
    FOREIGN KEY (CodigoUsuario) REFERENCES Usuarios (CodigoUsuario)
);
GO

CREATE TABLE IndicadoresEstrategicos
(
    IdIndicador         uniqueidentifier NOT NULL,
    IdPei               uniqueidentifier NOT NULL,
    IdObjEstrategico    uniqueidentifier NOT NULL,
    IdAccionEstrategica uniqueidentifier NOT NULL,

    Codigo              int              NOT NULL,

    CodigoEstado        char(1)          NOT NULL,

    PRIMARY KEY (IdIndicador),

    CONSTRAINT chk_Codigo_IndicadorEstrategico CHECK (Codigo >= 0),

    FOREIGN KEY (IdIndicador) REFERENCES Indicadores (IdIndicador) ON DELETE CASCADE,
    FOREIGN KEY (IdPei) REFERENCES Peis (IdPei),
    FOREIGN KEY (IdObjEstrategico) REFERENCES ObjetivosEstrategicos (IdObjEstrategico),
    foreign key (IdAccionEstrategica) references AccionesEstrategicas (IdAccionEstrategica),
    FOREIGN KEY (CodigoEstado) REFERENCES Estados (CodigoEstado),
);
GO

CREATE UNIQUE INDEX UQ_IndicadorEstrategico_Pei_Codigo_Vigente
    ON dbo.IndicadoresEstrategicos (IdPei, Codigo) WHERE CodigoEstado = 'V';
GO



CREATE TABLE Auditoria_IndEstrategico
(
    IdAuditoria   INT IDENTITY(1,1) PRIMARY KEY,
    IdIndicador   UNIQUEIDENTIFIER NOT NULL,
    Operacion     VARCHAR(10)      NOT NULL,
    TipoOperacion Varchar(10)      not null,
    Usuario       NVARCHAR(100) NULL,
    IPCliente     VARCHAR(50) NULL,
    Fecha         DATETIME         NOT NULL DEFAULT GETDATE(),
    DatosAntes    NVARCHAR(MAX) NULL,
    DatosDespues  NVARCHAR(MAX) NULL
);
GO

CREATE TRIGGER trg_Insert_IndEstrategico
    ON dbo.IndicadoresEstrategicos AFTER INSERT
AS
BEGIN
    SET
NOCOUNT ON;

INSERT INTO dbo.Auditoria_IndEstrategico (IdIndicador,
                                          Operacion,
                                          TipoOperacion,
                                          Usuario,
                                          IPCliente,
                                          DatosDespues)
SELECT i.IdIndicador,
       'INSERT',
       'INSERT',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),
       (SELECT '{"IdIndicador":' + ISNULL(CAST(ie.IdIndicador AS VARCHAR(20)), 'null') +
               ',"IdPei":' + ISNULL(CAST(ie.IdPei AS VARCHAR(20)), 'null') +
               ',"IdObjEstrategico":' + ISNULL(CAST(ie.IdObjEstrategico AS VARCHAR(20)), 'null') +
               ',"Codigo":' + ISNULL('"' + REPLACE(ie.Codigo, '"', '\"') + '"', 'null') +
               ',"Meta":' + ISNULL('"' + REPLACE(ind.Meta, '"', '\"') + '"', 'null') +
               ',"Descripcion":' + ISNULL('"' + REPLACE(ind.Descripcion, '"', '\"') + '"', 'null') +
               ',"LineaBase":' + ISNULL('"' + REPLACE(ind.LineaBase, '"', '\"') + '"', 'null') +
               ',"IdTipoResultado":' + ISNULL(CAST(ind.IdTipoResultado AS VARCHAR(20)), 'null') +
               ',"IdCategoriaIndicador":' + ISNULL(CAST(ind.IdCategoriaIndicador AS VARCHAR(20)), 'null') +
               ',"IdUnidadIndicador":' + ISNULL(CAST(ind.IdUnidadIndicador AS VARCHAR(20)), 'null') +
               ',"CodigoEstado":' + ISNULL('"' + REPLACE(ind.CodigoEstado, '"', '\"') + '"', 'null') +
               ',"FechaHoraRegistro":' + ISNULL('"' + CONVERT(VARCHAR (19), ind.FechaHoraRegistro, 120) + '"', 'null') +
               ',"CodigoUsuario":' + ISNULL('"' + REPLACE(ind.CodigoUsuario, '"', '\"') + '"', 'null') +
               '}'
        FROM dbo.IndicadoresEstrategicos ie
                 INNER JOIN dbo.Indicadores ind
                            ON ind.IdIndicador = ie.IdIndicador
        WHERE ie.IdIndicador = i.IdIndicador)

FROM inserted i;
END;
GO

CREATE TRIGGER trg_Update_IndEstrategico
    ON dbo.IndicadoresEstrategicos
    AFTER
UPDATE
    AS
BEGIN
    SET
NOCOUNT ON;

INSERT INTO dbo.Auditoria_IndEstrategico (IdIndicador,
                                          Operacion,
                                          TipoOperacion,
                                          Usuario,
                                          IPCliente,
                                          DatosAntes,
                                          DatosDespues)
SELECT d.IdIndicador,
       CASE
           WHEN d.CodigoEstado = 'V'
               AND i.CodigoEstado <> 'V'
               THEN 'DELETE'
           ELSE 'UPDATE'
           END,
       'LOGICO',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),
       (SELECT '{"IdIndicador":' + ISNULL(CAST(d.IdIndicador AS VARCHAR(20)), 'null') +
               ',"IdPei":' + ISNULL(CAST(d.IdPei AS VARCHAR(20)), 'null') +
               ',"IdObjEstrategico":' + ISNULL(CAST(d.IdObjEstrategico AS VARCHAR(20)), 'null') +
               ',"Codigo":' + ISNULL('"' + REPLACE(d.Codigo, '"', '\"') + '"', 'null') +
               ',"Meta":' + ISNULL('"' + REPLACE(indAntes.Meta, '"', '\"') + '"', 'null') +
               ',"Descripcion":' + ISNULL('"' + REPLACE(indAntes.Descripcion, '"', '\"') + '"', 'null') +
               ',"LineaBase":' + ISNULL('"' + REPLACE(indAntes.LineaBase, '"', '\"') + '"', 'null') +
               ',"IdTipoResultado":' + ISNULL(CAST(indAntes.IdTipoResultado AS VARCHAR(20)), 'null') +
               ',"IdCategoriaIndicador":' + ISNULL(CAST(indAntes.IdCategoriaIndicador AS VARCHAR(20)), 'null') +
               ',"IdUnidadIndicador":' + ISNULL(CAST(indAntes.IdUnidadIndicador AS VARCHAR(20)), 'null') +
               ',"CodigoEstado":' + ISNULL('"' + REPLACE(indAntes.CodigoEstado, '"', '\"') + '"', 'null') +
               ',"FechaHoraRegistro":' +
               ISNULL('"' + CONVERT(VARCHAR (19), indAntes.FechaHoraRegistro, 120) + '"', 'null') +
               ',"CodigoUsuario":' + ISNULL('"' + REPLACE(indAntes.CodigoUsuario, '"', '\"') + '"', 'null') +
               '}'
        FROM dbo.Indicadores indAntes
        WHERE indAntes.IdIndicador = d.IdIndicador),
       (SELECT '{"IdIndicador":' + ISNULL(CAST(i.IdIndicador AS VARCHAR(20)), 'null') +
               ',"IdPei":' + ISNULL(CAST(i.IdPei AS VARCHAR(20)), 'null') +
               ',"IdObjEstrategico":' + ISNULL(CAST(i.IdObjEstrategico AS VARCHAR(20)), 'null') +
               ',"Codigo":' + ISNULL('"' + REPLACE(i.Codigo, '"', '\"') + '"', 'null') +
               ',"Meta":' + ISNULL('"' + REPLACE(indDespues.Meta, '"', '\"') + '"', 'null') +
               ',"Descripcion":' + ISNULL('"' + REPLACE(indDespues.Descripcion, '"', '\"') + '"', 'null') +
               ',"LineaBase":' + ISNULL('"' + REPLACE(indDespues.LineaBase, '"', '\"') + '"', 'null') +
               ',"IdTipoResultado":' + ISNULL(CAST(indDespues.IdTipoResultado AS VARCHAR(20)), 'null') +
               ',"IdCategoriaIndicador":' + ISNULL(CAST(indDespues.IdCategoriaIndicador AS VARCHAR(20)), 'null') +
               ',"IdUnidadIndicador":' + ISNULL(CAST(indDespues.IdUnidadIndicador AS VARCHAR(20)), 'null') +
               ',"CodigoEstado":' + ISNULL('"' + REPLACE(indDespues.CodigoEstado, '"', '\"') + '"', 'null') +
               ',"FechaHoraRegistro":' +
               ISNULL('"' + CONVERT(VARCHAR (19), indDespues.FechaHoraRegistro, 120) + '"', 'null') +
               ',"CodigoUsuario":' + ISNULL('"' + REPLACE(indDespues.CodigoUsuario, '"', '\"') + '"', 'null') +
               '}'
        FROM dbo.Indicadores indDespues
        WHERE indDespues.IdIndicador = i.IdIndicador)

FROM deleted d
         INNER JOIN inserted i
                    ON i.IdIndicador = d.IdIndicador;
END;
GO

CREATE TRIGGER trg_Update_Indicador
    ON dbo.Indicadores
    AFTER
UPDATE
    AS
BEGIN
    SET
NOCOUNT ON;

INSERT INTO dbo.Auditoria_IndEstrategico (IdIndicador,
                                          Operacion,
                                          TipoOperacion,
                                          Usuario,
                                          IPCliente,
                                          DatosAntes,
                                          DatosDespues)
SELECT i.IdIndicador,
       CASE
           WHEN d.CodigoEstado = 'V'
               AND i.CodigoEstado <> 'V'
               THEN 'DELETE'
           ELSE 'UPDATE'
           END,
       'LOGICO',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),
       (SELECT '{"IdIndicador":' + ISNULL(CAST(ie.IdIndicador AS VARCHAR(20)), 'null') +
               ',"IdPei":' + ISNULL(CAST(ie.IdPei AS VARCHAR(20)), 'null') +
               ',"IdObjEstrategico":' + ISNULL(CAST(ie.IdObjEstrategico AS VARCHAR(20)), 'null') +
               ',"Codigo":' + ISNULL('"' + REPLACE(ie.Codigo, '"', '\"') + '"', 'null') +
               ',"Meta":' + ISNULL('"' + REPLACE(d.Meta, '"', '\"') + '"', 'null') +
               ',"Descripcion":' + ISNULL('"' + REPLACE(d.Descripcion, '"', '\"') + '"', 'null') +
               ',"LineaBase":' + ISNULL('"' + REPLACE(d.LineaBase, '"', '\"') + '"', 'null') +
               ',"IdTipoResultado":' + ISNULL(CAST(d.IdTipoResultado AS VARCHAR(20)), 'null') +
               ',"IdCategoriaIndicador":' + ISNULL(CAST(d.IdCategoriaIndicador AS VARCHAR(20)), 'null') +
               ',"IdUnidadIndicador":' + ISNULL(CAST(d.IdUnidadIndicador AS VARCHAR(20)), 'null') +
               ',"CodigoEstado":' + ISNULL('"' + REPLACE(d.CodigoEstado, '"', '\"') + '"', 'null') +
               ',"FechaHoraRegistro":' + ISNULL('"' + CONVERT(VARCHAR (19), d.FechaHoraRegistro, 120) + '"', 'null') +
               ',"CodigoUsuario":' + ISNULL('"' + REPLACE(d.CodigoUsuario, '"', '\"') + '"', 'null') +
               '}'
        FROM dbo.IndicadoresEstrategicos ie
        WHERE ie.IdIndicador = d.IdIndicador),
       (SELECT '{"IdIndicador":' + ISNULL(CAST(ie.IdIndicador AS VARCHAR(20)), 'null') +
               ',"IdPei":' + ISNULL(CAST(ie.IdPei AS VARCHAR(20)), 'null') +
               ',"IdObjEstrategico":' + ISNULL(CAST(ie.IdObjEstrategico AS VARCHAR(20)), 'null') +
               ',"Codigo":' + ISNULL('"' + REPLACE(ie.Codigo, '"', '\"') + '"', 'null') +
               ',"Meta":' + ISNULL('"' + REPLACE(i.Meta, '"', '\"') + '"', 'null') +
               ',"Descripcion":' + ISNULL('"' + REPLACE(i.Descripcion, '"', '\"') + '"', 'null') +
               ',"LineaBase":' + ISNULL('"' + REPLACE(i.LineaBase, '"', '\"') + '"', 'null') +
               ',"IdTipoResultado":' + ISNULL(CAST(i.IdTipoResultado AS VARCHAR(20)), 'null') +
               ',"IdCategoriaIndicador":' + ISNULL(CAST(i.IdCategoriaIndicador AS VARCHAR(20)), 'null') +
               ',"IdUnidadIndicador":' + ISNULL(CAST(i.IdUnidadIndicador AS VARCHAR(20)), 'null') +
               ',"CodigoEstado":' + ISNULL('"' + REPLACE(i.CodigoEstado, '"', '\"') + '"', 'null') +
               ',"FechaHoraRegistro":' + ISNULL('"' + CONVERT(VARCHAR (19), i.FechaHoraRegistro, 120) + '"', 'null') +
               ',"CodigoUsuario":' + ISNULL('"' + REPLACE(i.CodigoUsuario, '"', '\"') + '"', 'null') +
               '}'
        FROM dbo.IndicadoresEstrategicos ie
        WHERE ie.IdIndicador = i.IdIndicador)
FROM deleted d
         INNER JOIN inserted i
                    ON i.IdIndicador = d.IdIndicador

WHERE EXISTS (SELECT 1
              FROM dbo.IndicadoresEstrategicos ie
              WHERE ie.IdIndicador = i.IdIndicador);
END;
GO

/*****/

CREATE TRIGGER trg_Delete_IndEstrategico
    ON dbo.IndicadoresEstrategicos
    AFTER
DELETE
AS
BEGIN
    SET
NOCOUNT ON;

INSERT INTO dbo.Auditoria_IndEstrategico (IdIndicador,
                                          Operacion,
                                          TipoOperacion,
                                          Usuario,
                                          IPCliente,
                                          DatosAntes)
SELECT d.IdIndicador,
       'DELETE',
       'FISICA',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),
       (SELECT '{"IdIndicador":' + ISNULL(CAST(d.IdIndicador AS VARCHAR(20)), 'null') +
               ',"IdPei":' + ISNULL(CAST(d.IdPei AS VARCHAR(20)), 'null') +
               ',"IdObjEstrategico":' + ISNULL(CAST(d.IdObjEstrategico AS VARCHAR(20)), 'null') +
               ',"Codigo":' + ISNULL('"' + REPLACE(d.Codigo, '"', '\"') + '"', 'null') +
               '}')

FROM deleted d;
END;
GO

CREATE TRIGGER trg_Delete_Indicador
    ON dbo.Indicadores
    AFTER
DELETE
AS
BEGIN
    SET
NOCOUNT ON;

INSERT INTO dbo.Auditoria_IndEstrategico (IdIndicador,
                                          Operacion,
                                          TipoOperacion,
                                          Usuario,
                                          IPCliente,
                                          DatosAntes)
SELECT d.IdIndicador,
       'DELETE',
       'FISICA',
       SYSTEM_USER,
       dbo.ObtenerIPCliente(),

       (SELECT '{"IdIndicador":' + ISNULL(CAST(d.IdIndicador AS VARCHAR(20)), 'null') +
               ',"Meta":' + ISNULL('"' + REPLACE(d.Meta, '"', '\"') + '"', 'null') +
               ',"Descripcion":' + ISNULL('"' + REPLACE(d.Descripcion, '"', '\"') + '"', 'null') +
               ',"LineaBase":' + ISNULL('"' + REPLACE(d.LineaBase, '"', '\"') + '"', 'null') +
               ',"IdTipoResultado":' + ISNULL(CAST(d.IdTipoResultado AS VARCHAR(20)), 'null') +
               ',"IdCategoriaIndicador":' + ISNULL(CAST(d.IdCategoriaIndicador AS VARCHAR(20)), 'null') +
               ',"IdUnidadIndicador":' + ISNULL(CAST(d.IdUnidadIndicador AS VARCHAR(20)), 'null') +
               ',"CodigoEstado":' + ISNULL('"' + REPLACE(d.CodigoEstado, '"', '\"') + '"', 'null') +
               ',"FechaHoraRegistro":' + ISNULL('"' + CONVERT(VARCHAR (19), d.FechaHoraRegistro, 120) + '"', 'null') +
               ',"CodigoUsuario":' + ISNULL('"' + REPLACE(d.CodigoUsuario, '"', '\"') + '"', 'null') +
               '}')

FROM deleted d;
END;
GO



/**
  estructura
  */
create table Programas
(
    IdPrograma        uniqueidentifier      default newsequentialid() primary key,
    Codigo            Char(3)      not null,
    Descripcion       varchar(500) NOT NULL,
    CodigoEstado      char(1)      not null,
    FechaHoraRegistro datetime     not null default getdate(),
    CodigoUsuario     char(3)      not null,

    constraint chk_Programas_Codigo check (Codigo like '[0-9][0-9][0-9]'),
    constraint chk_Programas_Descripcion check (Descripcion <> ''),

    foreign key (CodigoEstado) references Estados (CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios (CodigoUsuario)
)

CREATE UNIQUE INDEX [UQ_Programa_Codigo]
    ON [dbo].Programas(Codigo)
    WHERE ([CodigoEstado] = 'V');

create table Proyectos
(
    IdProyecto        uniqueidentifier          default newsequentialid() primary key,
    IdPrograma        uniqueidentifier not null,
    Codigo            varchar(20)      not null,
    Descripcion       varchar(500)     NOT NULL,
    CodigoEstado      char(1)          not null,
    FechaHoraRegistro datetime         not null default getdate(),
    CodigoUsuario     char(3)          not null,

    constraint chk_Proyectos_Codigo check (Codigo NOT LIKE '%[^0-9]%' AND LEN(Codigo) > 2
        ),
    constraint chk_Proyectos_Descripcion check (Descripcion <> ''),

    foreign key (IdPrograma) references Programas (IdPrograma),
    foreign key (CodigoEstado) references Estados (CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios (CodigoUsuario)
)

CREATE UNIQUE INDEX [UQ_Proyecto_Codigo]
    ON [dbo].Proyectos(IdPrograma,Codigo)
    WHERE ([CodigoEstado] = 'V');

create table Actividades
(
    IdActividad       uniqueidentifier          default newsequentialid() primary key,
    IdPrograma        uniqueidentifier not null,
    Codigo            char(3)          not null,
    Descripcion       varchar(500)     NOT NULL,
    CodigoEstado      char(1)          not null,
    FechaHoraRegistro datetime         not null default getdate(),
    CodigoUsuario     char(3)          not null,

    constraint chk_Actividades_Codigo check (Codigo like '[0-9][0-9][0-9]'),
    constraint chk_Actividades_Descripcion check (Descripcion <> ''),

    foreign key (IdPrograma) references Programas (IdPrograma),
    foreign key (CodigoEstado) references Estados (CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios (CodigoUsuario)
)

CREATE UNIQUE INDEX [UQ_Actividad_Codigo]
    ON [dbo].Actividades(IdPrograma,Codigo)
    WHERE ([CodigoEstado] = 'V');


/**
 *  CATALOGOS
 */

create table Estados
(
    CodigoEstado char(1) primary key,
    NombreEstado Varchar(50) not null
) go

insert into Estados values('V','Vigente')
insert into Estados values('C','Caduco')
insert into Estados values('E','Eliminado')

go

create table CatTiposResultados
(
    IdTipoResultado   uniqueidentifier      default newsequentialid() primary key,
    Descripcion       Varchar(250) not null,
    CodigoEstado      char(1)      not null,
    FechaHoraRegistro datetime     not null default getdate(),
    CodigoUsuario     char(3)      not null,

    foreign key (CodigoEstado) references Estados (CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios (CodigoUsuario)
) go

insert into CatTiposResultados(Descripcion,CodigoEstado,CodigoUsuario) values('Bien','V','ADM')
insert into CatTiposResultados(Descripcion,CodigoEstado,CodigoUsuario) values('Norma','V','ADM')
insert into CatTiposResultados(Descripcion,CodigoEstado,CodigoUsuario) values('Servicio','V','ADM')

go

create table CatCategoriasIndicadores
(
    IdCategoriaIndicador uniqueidentifier      default newsequentialid() primary key,
    Descripcion          Varchar(250) not null,
    CodigoEstado         char(1)      not null,
    FechaHoraRegistro    datetime     not null default getdate(),
    CodigoUsuario        char(3)      not null,

    foreign key (CodigoEstado) references Estados (CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios (CodigoUsuario)
) go

insert into CatCategoriasIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Proceso','V','ADM')
insert into CatCategoriasIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Producto','V','ADM')
insert into CatCategoriasIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Recursos Financieros','V','ADM')
insert into CatCategoriasIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Recursos Fisicos','V','ADM')
insert into CatCategoriasIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Recursos Humanos','V','ADM')

go

create table CatUnidadesIndicadores
(
    IdUnidadIndicador uniqueidentifier      default newsequentialid() primary key,
    Descripcion       Varchar(250) not null,
    CodigoEstado      char(1)      not null,
    FechaHoraRegistro datetime     not null default getdate(),
    CodigoUsuario     char(3)      not null,

    foreign key (CodigoEstado) references Estados (CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios (CodigoUsuario)
) go

insert into CatUnidadesIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Numero','V','ADM')
insert into CatUnidadesIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Porcentaje','V','ADM')


/*

create table IndicadoresEstrategicos(
    CodigoIndicador int primary key not null,
    Codigo int not null,
    Meta int not null,
    Descripcion Varchar(250) not null,
    ObjetivoEstrategico int not null,
    Resultado int not null,
    TipoIndicador int not null,
    Categoria int not null,
    Unidad int not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    constraint chk_CodigoIndicadorEstrategico check (Codigo > 0),
    constraint chk_DescripcionIndicadorEstrategico check (Descripcion != ''),

    foreign key (ObjetivoEstrategico) references ObjetivosEstrategicos(CodigoObjEstrategico),
    foreign key (Resultado) references TiposResultados(CodigoTipo),
    foreign key (TipoIndicador) references TiposIndicadores(CodigoTipo),
    foreign key (Categoria) references CategoriasIndicadores(CodigoCategoria),
    foreign key (Unidad) references IndicadoresUnidades(CodigoTipo),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

CREATE UNIQUE INDEX [UQ_Codigo]
    ON [dbo].IndicadoresEstrategicos(Codigo)
    WHERE   ([CodigoEstado] = 'V');


create table IndicadoresEstrategicosGestiones(
    CodigoProgramacionGestion int identity(1,1) primary key,
    IndicadorEstrategico int not null,
    Gestion int not null,
    Meta int not null,

    unique(Gestion,IndicadorEstrategico),

    foreign key (IndicadorEstrategico) references IndicadoresEstrategicos(CodigoIndicador),
)

create table Unidades
(
    CodigoUnidad int primary key not null,
    Da varchar(20) not null,
    Ue varchar(20) not null,
    Descripcion varchar(250) not null,
    FechaInicio date not null,
    FechaFin date not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    constraint chk_Da check (Da != ''),
    constraint chk_Ue check (Ue != ''),
    constraint chk_Date check (FechaInicio < FechaFin),

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

CREATE UNIQUE INDEX [UQ_Apertura]
    ON [dbo].Unidades(Da,Ue)
    WHERE   ([CodigoEstado] = 'V');

create table Programas
(
    CodigoPrograma int primary key not null,
    Codigo varchar(20) not null,
    Descripcion varchar(250) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

CREATE UNIQUE INDEX [UQ_Codigo]
    ON [dbo].Programas(Codigo)
    WHERE   ([CodigoEstado] = 'V');

create table LlavePresupuestaria(
                                    CodigoUnidad int not null,
                                    CodigoPrograma int not null,
                                    CodigoProyecto int not null,
                                    CodigoActividad int not null,
                                    Descripcion Varchar(250) not null,
                                    TechoPresupuestario float not null,
                                    FechaInicio Datetime not null,
                                    FechaFin Datetime,
                                    CodigoEstado char(1) not null,
                                    FechaHoraRegistro datetime not null default getdate(),
                                    CodigoUsuario char(3) not null,

                                    constraint chk_LlaveFechas check (FechaInicio > FechaFin),

                                    primary key (CodigoUnidad,CodigoPrograma,CodigoProyecto,CodigoActividad),
                                    foreign key (CodigoEstado) references Estados(CodigoEstado),
                                    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)




create table ObjetivosInstitucionales(
    CodigoObjInstitucional int primary key not null,
    CodigoCOGE char(2) not null,
    Objetivo varchar(200) not null,
    CodigoObjEstrategico int not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoObjEstrategico) references ObjetivosEstrategicos(CodigoObjEstrategico),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

create table ObjetivosEspecificos(
    CodigoObjEspecifico int primary key not null,
    CodigoCOGE char(2) not null,
    Objetivo varchar(200) not null,
    CodigoObjInstitucional int not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoObjInstitucional) references ObjetivosInstitucionales(CodigoObjInstitucional),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

create table TiposArticulaciones(
    CodigoTipo int primary key not null,
    Descripcion Varchar(200) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

insert into TiposArticulaciones values(1,'POA','V',default,'ADM')
insert into TiposArticulaciones values(2,'PEI','V',default,'ADM')

go

create table TiposResultados(
    CodigoTipo int primary key not null,
    Descripcion Varchar(200) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

insert into TiposResultados values(1,'Bien','V',default,'ADM')
insert into TiposResultados values(2,'Norma','V',default,'ADM')
insert into TiposResultados values(3,'Servicio','V',default,'ADM')

go

create table TiposIndicadores(
    CodigoTipo int primary key not null,
    Descripcion Varchar(200) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

insert into TiposIndicadores values(1,'Gestion','V',default,'ADM')
insert into TiposIndicadores values(2,'Resultado','V',default,'ADM')

go

create table CategoriasIndicadores(
    CodigoCategoria int primary key not null,
    Descripcion Varchar(200) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

insert into CategoriasIndicadores values(1,'Proceso','V',default,'ADM')
insert into CategoriasIndicadores values(2,'Producto','V',default,'ADM')
insert into CategoriasIndicadores values(3,'Recursos Financieros','V',default,'ADM')
insert into CategoriasIndicadores values(4,'Recursos Fisicos','V',default,'ADM')
insert into CategoriasIndicadores values(5,'Recursos Humanos','V',default,'ADM')

go

create table IndicadoresUnidades(
    CodigoTipo int primary key not null,
    Descripcion Varchar(200) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

    go

insert into IndicadoresUnidades values(1,'Numero','V',default,'ADM')
insert into IndicadoresUnidades values(2,'Porcentaje','V',default,'ADM')

go

create table Indicadores(
    CodigoIndicador int primary key not null,
    CodigoPei varchar(3),
    CodigoPoa varchar(3),
    Descripcion Varchar(200) not null,
    Gestion int not null,
    ObjetivoEspecifico int not null,
    Actividad int not null,
    Articulacion int not null,
    Resultado int not null,
    TipoIndicador int not null,
    Categoria int not null,
    Unidad int not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (ObjetivoEspecifico) references ObjetivosEspecificos(CodigoObjEspecifico),
    foreign key (Actividad) references Actividades(CodigoActividad),
    foreign key (Articulacion) references TiposArticulaciones(CodigoTipo),
    foreign key (Resultado) references TiposResultados(CodigoTipo),
    foreign key (TipoIndicador) references TiposIndicadores(CodigoTipo),
    foreign key (Categoria) references CategoriasIndicadores(CodigoCategoria),
    foreign key (Unidad) references IndicadoresUnidades(CodigoTipo),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

create table Actividades
(
    CodigoActividad int primary key not null,
    Programa int not null,
    Codigo varchar(20) not null,
    Descripcion varchar(250) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (Programa) references Programas(CodigoPrograma),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

create table Proyectos
(
    CodigoProyecto int primary key not null,
    Programa int not null,
    Codigo varchar(20) not null,
    Descripcion varchar(250) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (Programa) references Programas(CodigoPrograma),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go



go



go

create table  IndicadoresAperturas
(
    CodigoIndicadorApertura int primary key not null,
    Indicador int not null,
    Apertura int not null,
    MetaObligatoria int not null default 0,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (Indicador) references Indicadores(CodigoIndicador),
    foreign key (Apertura) references Unidades(CodigoUnidad),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)


create table AperturasProgramaticas
(
    CodigoAperturaProgramatica int primary key  not null,
    Unidad int not null,
    Programa int not null,
    Proyecto int not null,
    Actividad int not null,
    Descripcion varchar(250) not null,
    Organizacional bit not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    unique(Unidad, Programa, Proyecto, Actividad),
    foreign key (Unidad) references Unidades(CodigoUnidad),
    foreign key (Programa) references Programas(CodigoPrograma),
    foreign key (Proyecto) references Proyectos(CodigoProyecto),
    foreign key (Actividad) references Actividades(CodigoActividad),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

create table Gastos(
    CodigoGasto int not null primary key identity(1,1),
    Descripcion Varchar(450) not null,
    EntidadTransferencia varchar(5) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    constraint chk_Gastos_Descripcion check (Descripcion != ''),

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

*/
