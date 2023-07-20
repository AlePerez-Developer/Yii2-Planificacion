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


create table AperturasProgramaticas
(
    CodigoAperturaProgramatica int primary key identity(1,1) not null,
    Da char(2) not null,
    Ue char(3) not null,
    Prg char(3) not null,
    Descripcion varchar(250) not null,
    FechaInicio date not null,
    FechaFin date not null,
    Organizacional bit not null,
    Operaciones bit not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

create table Proyectos
(
    CodigoProyecto int primary key identity(1,1) not null,
    Codigo varchar(20) not null,
    Descripcion varchar(250) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)

create table Actividades
(
    CodigoActividad int primary key identity(1,1) not null,
    Codigo varchar(20) not null,
    Descripcion varchar(250) not null,
    CodigoEstado char(1) not null,
    FechaHoraRegistro datetime not null default getdate(),
    CodigoUsuario char(3) not null,

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)
