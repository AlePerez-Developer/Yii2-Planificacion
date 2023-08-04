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

create table Estados(
    CodigoEstado char(1) primary key,
    NombreEstado Varchar(50) not null
)

create table PEIs(
    CodigoPei int primary key,
    Descripcion Varchar(250) not null,
    FechaAprobacion Date not null,
    GestionInicio int not null,
    GestionFin int not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

create table ObjetivosEstrategicos(
    CodigoObjEstrategico int primary key not null,
    CodigoCOGE char(3) not null,
    Objetivo varchar(200) not null,
    CodigoPei int not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoPei) references Peis(CodigoPei),
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

create table Proyectos
(
    CodigoProyecto int primary key not null,
    Codigo varchar(20) not null,
    Descripcion varchar(250) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

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


