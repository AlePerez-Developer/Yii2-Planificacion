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
    CodigoUsuario char(3) not null

    foreign key (CodigoEstado) references Estados(CodigoEstado),
    foreign key (CodigoUsuario) references Usuarios(CodigoUsuario)
)
