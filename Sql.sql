
CREATE FUNCTION dbo.ObtenerIPCliente()
    RETURNS VARCHAR(50)
AS
BEGIN
    DECLARE @ip VARCHAR(50);
SELECT @ip = client_net_address
FROM sys.dm_exec_connections
WHERE session_id = @@SPID;
RETURN @ip;
END;

CREATE TABLE Usuarios(
    CodigoUsuario char(3) primary key,
    CodigoTrabajador char(10) NULL,
    IdPersona char(15) not null,
    Login varchar(20) not null,
    Llave char(32) not null,
    Email varchar(100) not null,
    Pwd varchar(100) not null,
    Foto varchar(100) not null,
    CodigoRol char(3) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),

    foreign key (CodigoEstado) references Estados(CodigoEstado),
)


go

create table PEIs(
    IdPei uniqueidentifier DEFAULT NEWSEQUENTIALID() PRIMARY KEY,
    Descripcion Varchar(500) not null,
    FechaAprobacion Date not null,
    GestionInicio int not null,
    GestionFin int not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    constraint chk_GInicio check (GestionInicio > 2000),
    constraint chk_GFin check (GestionFin > 2001),
    constraint chk_Gestion check (GestionInicio < GestionFin),
    constraint chk_Descripcion check (Descripcion != ''),

	foreign key (CodigoEstado) references Estados(CodigoEstado),
	foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

CREATE TRIGGER trg_Insert_Peis
    ON Peis
    AFTER INSERT
AS
    BEGIN
        INSERT INTO Auditoria_Peis (IdPei, Operacion, Usuario, IPCliente, DatosDespues)
        SELECT i.IdPei, 'INSERT', SYSTEM_USER, dbo.ObtenerIPCliente(),
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
    AFTER UPDATE
AS
    BEGIN
        INSERT INTO Auditoria_Peis (IdPei, Operacion, Usuario, IPCliente, DatosAntes, DatosDespues)
        SELECT d.IdPei, 'UPDATE', SYSTEM_USER,dbo.ObtenerIPCliente(),
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
    AFTER DELETE
AS
    BEGIN
        INSERT INTO Auditoria_Peis (IdPei, Operacion, Usuario, IPCliente, DatosAntes)
        SELECT d.IdPei, 'DELETE', SYSTEM_USER,dbo.ObtenerIPCliente(),
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

CREATE TABLE Auditoria_Peis (
    IdAuditoria INT IDENTITY(1,1) PRIMARY KEY,
    IdPei UNIQUEIDENTIFIER,
    Operacion VARCHAR(10),         -- 'INSERT', 'UPDATE', 'DELETE'
    Usuario NVARCHAR(100),         -- Usuario que realizó la acción
    IPCliente VARCHAR(50),         -- Dirección IP del cliente
    Fecha DATETIME DEFAULT GETDATE(),
    DatosAntes NVARCHAR(MAX),      -- JSON opcional con estado anterior
    DatosDespues NVARCHAR(MAX)     -- JSON opcional con estado nuevo
)

go

create table AreasEstrategicas (
    IdAreaEstrategica uniqueidentifier DEFAULT NEWSEQUENTIALID() PRIMARY KEY,
    IdPei UNIQUEIDENTIFIER NOT NULL,
    Codigo int not null,
    Descripcion Varchar(500) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    constraint chk_Codigo_Area_Estrategica check (Codigo > 0),
    constraint chk_Descripcion_Area_Estrategica check (Descripcion != ''),

    foreign key (IdPei) references Peis(IdPei),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

CREATE UNIQUE INDEX [UQ_Area_Estrategica_Pei]
    ON [dbo].AreasEstrategicas(Codigo,IdPei)
    WHERE   ([CodigoEstado] = 'V');

go

create table PoliticasEstrategicas (
    IdPoliticaEstrategica uniqueidentifier DEFAULT NEWSEQUENTIALID() PRIMARY KEY,
    IdAreaEstrategica UNIQUEIDENTIFIER NOT NULL,
    Codigo int not null,
    Descripcion Varchar(500) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    constraint chk_Codigo_Politica_Estrategica check (Codigo != ''),
    constraint chk_Descripcion_Politica_Estrategica check (Descripcion != ''),

    foreign key (IdAreaEstrategica) references AreasEstrategicas(IdAreaEstrategica),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

CREATE UNIQUE INDEX [UQ_Politica_Estrategica_Area_Estrategica]
    ON [dbo].PoliticasEstrategicas(Codigo,IdAreaEstrategica)
    WHERE   ([CodigoEstado] = 'V');

go

create table ObjetivosEstrategicos(
    IdObjEstrategico uniqueidentifier DEFAULT NEWSEQUENTIALID() PRIMARY KEY,
    IdAreaEstrategica UNIQUEIDENTIFIER not null,
    IdPoliticaEstrategica UNIQUEIDENTIFIER not null,
    Codigo int not null,
    Objetivo varchar(500) not null,
    Producto varchar(500) not null,
    Indicador_Descripcion varchar(500) not null,
    Indicador_Formula varchar(500) not null,
    IdPei UNIQUEIDENTIFIER not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    constraint chk_Codigo_Objetivo_Estrategico check (Codigo > 0),
    constraint chk_Objetivo_Objetivo_Estrategico check (Objetivo != ''),
    constraint chk_Resultado_Objetivo_Estrategico check (Producto != ''),
    constraint chk_Indicador_Descripcion_Objetivo_Estrategico check (Indicador_Descripcion != ''),
    constraint chk_Indicador_Formula_Objetivo_Estrategico check (Indicador_Formula != ''),

    foreign key (IdAreaEstrategica) references AreasEstrategicas(IdAreaEstrategica),
    foreign key (IdPoliticaEstrategica) references PoliticasEstrategicas(IdPoliticaEstrategica),
    foreign key (IdPei) references Peis(IdPei),
    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

CREATE UNIQUE INDEX [UQ_Objetivo_Area_Politica]
    ON [dbo].ObjetivosEstrategicos(IdPei, IdAreaEstrategica, IdPoliticaEstrategica, Codigo)
    WHERE   ([CodigoEstado] = 'V');

go

CREATE TRIGGER trg_Insert_ObjEstrategico
ON ObjetivosEstrategicos
AFTER INSERT
AS
BEGIN
INSERT INTO Auditoria_ObjEstrategico (IdObjEstrategico, Operacion, Usuario, IPCliente, DatosDespues)
SELECT i.IdObjEstrategico, 'INSERT', SYSTEM_USER, dbo.ObtenerIPCliente(),
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
AFTER UPDATE
AS
BEGIN
INSERT INTO Auditoria_ObjEstrategico (IdObjEstrategico, Operacion, Usuario, IPCliente, DatosAntes, DatosDespues)
SELECT d.IdObjEstrategico, 'UPDATE', SYSTEM_USER,dbo.ObtenerIPCliente(),
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
AFTER DELETE
AS
BEGIN
INSERT INTO Auditoria_ObjEstrategico (IdObjEstrategico, Operacion, Usuario, IPCliente, DatosAntes)
SELECT d.IdObjEstrategico, 'DELETE', SYSTEM_USER,dbo.ObtenerIPCliente(),
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

CREATE TABLE Auditoria_ObjEstrategico (
    IdAuditoria INT IDENTITY(1,1) PRIMARY KEY,
    IdObjEstrategico UNIQUEIDENTIFIER,
    Operacion VARCHAR(10),         -- 'INSERT', 'UPDATE', 'DELETE'
    Usuario NVARCHAR(100),         -- Usuario que realizó la acción
    IPCliente VARCHAR(50),         -- Dirección IP del cliente
    Fecha DATETIME DEFAULT GETDATE(),
    DatosAntes NVARCHAR(MAX),      -- JSON opcional con estado anterior
    DatosDespues NVARCHAR(MAX)     -- JSON opcional con estado nuevo
)

go

create table IndicadoresEstrategicos(
    IdIndicadorEstrategico uniqueidentifier default newsequentialid() primary key,
    IdObjEstrategico uniqueidentifier not null,
    Codigo int not null,
    Meta int not null,
    Descripcion Varchar(500) not null,
    LineaBase int not null,

    IdTipoResultado uniqueidentifier not null,
    IdCategoriaIndicador uniqueidentifier not null,
    IdUnidadIndicador uniqueidentifier not null,

    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    constraint chk_Codigo_IndicadorEstrategico check (Codigo > 0),
    constraint chk_LineaBase_IndicadorEstrategico check (LineaBase > 0),
    constraint chk_DescripcionIndicadorEstrategico check (Descripcion != ''),

    foreign key (IdObjEstrategico) references ObjetivosEstrategicos(IdObjEstrategico),

    foreign key (IdTipoResultado) references CatTiposResultados(IdTipoResultado),
    foreign key (IdCategoriaIndicador) references CatCategoriasIndicadores(IdCategoriaIndicador),
    foreign key (IdUnidadIndicador) references CatUnidadesIndicadores(IdUnidadIndicador),

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

CREATE UNIQUE INDEX [UQ_Indicador_Objetivo_Codigo]
    ON [dbo].IndicadoresEstrategicos(Codigo)
    WHERE   ([CodigoEstado] = 'V');

go

CREATE TRIGGER trg_Insert_IndEstrategico
    ON IndicadoresEstrategicos
    AFTER INSERT
    AS
BEGIN
    INSERT INTO Auditoria_IndEstrategico (IdIndicadorEstrategico, Operacion, Usuario, IPCliente, DatosDespues)
    SELECT i.IdIndicadorEstrategico, 'INSERT', SYSTEM_USER, dbo.ObtenerIPCliente(),
       CONCAT('{IdObjEstrategico:"', i.IdObjEstrategico,
          '", Codigo:"', i.Codigo,
          '", Meta:"', i.Meta,
          '", Descripcion:"', i.Descripcion,
          '", LineaBase:"', i.LineaBase,
          '", TipoResultado:"', i.IdTipoResultado,
          '", CategoriaIndicador:"', i.IdCategoriaIndicador,
          '", UnidadIndicador:"', i.IdUnidadIndicador,
          '", Estado:"', i.CodigoEstado,
          '"}')
    FROM inserted i;
END;

go

CREATE TRIGGER trg_Update_IndEstrategico
    ON IndicadoresEstrategicos
    AFTER UPDATE
    AS
BEGIN
    INSERT INTO Auditoria_IndEstrategico (IdIndicadorEstrategico, Operacion, Usuario, IPCliente, DatosAntes, DatosDespues)
    SELECT d.IdIndicadorEstrategico, 'UPDATE', SYSTEM_USER,dbo.ObtenerIPCliente(),
       CONCAT('{IdObjEstrategico:"', d.IdObjEstrategico,
          '", Codigo:"', d.Codigo,
          '", Meta:"', d.Meta,
          '", Descripcion:"', d.Descripcion,
          '", LineaBase:"', d.LineaBase,
          '", TipoResultado:"', d.IdTipoResultado,
          '", CategoriaIndicador:"', d.IdCategoriaIndicador,
          '", UnidadIndicador:"', d.IdUnidadIndicador,
          '", Estado:"', d.CodigoEstado,
          '"}'),
       CONCAT('{IdObjEstrategico:"', i.IdObjEstrategico,
          '", Codigo:"', i.Codigo,
          '", Meta:"', i.Meta,
          '", Descripcion:"', i.Descripcion,
          '", LineaBase:"', i.LineaBase,
          '", TipoResultado:"', i.IdTipoResultado,
          '", CategoriaIndicador:"', i.IdCategoriaIndicador,
          '", UnidadIndicador:"', i.IdUnidadIndicador,
          '", Estado:"', i.CodigoEstado,
          '"}')
    FROM deleted d
         JOIN inserted i ON d.IdIndicadorEstrategico = i.IdIndicadorEstrategico;
END;

go

CREATE TRIGGER trg_Delete_IndEstrategico
    ON IndicadoresEstrategicos
    AFTER DELETE
    AS
BEGIN
    INSERT INTO Auditoria_IndEstrategico (IdIndicadorEstrategico, Operacion, Usuario, IPCliente, DatosAntes)
    SELECT d.IdIndicadorEstrategico, 'DELETE', SYSTEM_USER,dbo.ObtenerIPCliente(),
       CONCAT('{IdObjEstrategico:"', d.IdObjEstrategico,
          '", Codigo:"', d.Codigo,
          '", Meta:"', d.Meta,
          '", Descripcion:"', d.Descripcion,
          '", LineaBase:"', d.LineaBase,
          '", TipoResultado:"', d.IdTipoResultado,
          '", CategoriaIndicador:"', d.IdCategoriaIndicador,
          '", UnidadIndicador:"', d.IdUnidadIndicador,
          '", Estado:"', d.CodigoEstado,
          '"}')
    FROM deleted d;
END;

go

CREATE TABLE Auditoria_IndEstrategico (
    IdAuditoria INT IDENTITY(1,1) PRIMARY KEY,
    IdIndicadorEstrategico UNIQUEIDENTIFIER,
    Operacion VARCHAR(10),         -- 'INSERT', 'UPDATE', 'DELETE'
    Usuario NVARCHAR(100),         -- Usuario que realizó la acción
    IPCliente VARCHAR(50),         -- Dirección IP del cliente
    Fecha DATETIME DEFAULT GETDATE(),
    DatosAntes NVARCHAR(MAX),      -- JSON opcional con estado anterior
    DatosDespues NVARCHAR(MAX)     -- JSON opcional con estado nuevo
)








/**
 *  CATALOGOS
 */

create table Estados(
    CodigoEstado char(1) primary key,
    NombreEstado Varchar(50) not null
)

go

insert into Estados values('V','Vigente')
insert into Estados values('C','Caduco')
insert into Estados values('E','Eliminado')

go

create table CatTiposResultados
(
    IdTipoResultado uniqueidentifier default newsequentialid() primary key,
    Descripcion Varchar(250) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

insert into CatTiposResultados(Descripcion,CodigoEstado,CodigoUsuario) values('Bien','V','ADM')
insert into CatTiposResultados(Descripcion,CodigoEstado,CodigoUsuario) values('Norma','V','ADM')
insert into CatTiposResultados(Descripcion,CodigoEstado,CodigoUsuario) values('Servicio','V','ADM')

go

create table CatCategoriasIndicadores
(
    IdCategoriaIndicador uniqueidentifier default newsequentialid() primary key,
    Descripcion Varchar(250) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

insert into CatCategoriasIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Proceso','V','ADM')
insert into CatCategoriasIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Producto','V','ADM')
insert into CatCategoriasIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Recursos Financieros','V','ADM')
insert into CatCategoriasIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Recursos Fisicos','V','ADM')
insert into CatCategoriasIndicadores(Descripcion,CodigoEstado,CodigoUsuario) values('Recursos Humanos','V','ADM')

go

create table CatUnidadesIndicadores
(
    IdUnidadIndicador uniqueidentifier default newsequentialid() primary key,
    Descripcion Varchar(250) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

go

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
