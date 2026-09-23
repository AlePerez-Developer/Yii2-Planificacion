-- Migración de permisos de DA/llave a Unidad Ejecutora
-- Ejecutar en SQL Server sobre la base de Planificación.

IF OBJECT_ID('seguridad.UsuarioUnidadGestionEstado', 'U') IS NULL
BEGIN
    CREATE TABLE seguridad.UsuarioUnidadGestionEstado
    (
        IdUsuarioUnidadGestionEstado uniqueidentifier DEFAULT NEWSEQUENTIALID() PRIMARY KEY,
        IdUsuario uniqueidentifier NOT NULL,
        IdUnidadEjecutora uniqueidentifier NOT NULL,
        IdGestion uniqueidentifier NOT NULL,
        IdEstadoPoa uniqueidentifier NOT NULL,
        CodigoEstado char(1) NOT NULL,
        FechaHoraRegistro datetime NOT NULL DEFAULT GETDATE(),
        Usuario uniqueidentifier NOT NULL,
        FOREIGN KEY (IdUsuario) REFERENCES seguridad.Usuarios (IdUsuario),
        FOREIGN KEY (IdUnidadEjecutora) REFERENCES UnidadesEjecutoras (IdUnidadEjecutora),
        FOREIGN KEY (IdGestion) REFERENCES PeiGestion (IdGestion),
        FOREIGN KEY (IdEstadoPoa) REFERENCES seguridad.EstadosPoa (IdEstadoPoa),
        FOREIGN KEY (CodigoEstado) REFERENCES Estados (CodigoEstado),
        FOREIGN KEY (Usuario) REFERENCES seguridad.Usuarios (IdUsuario)
    );
END
GO

IF OBJECT_ID('seguridad.UsuarioDaGestionEstado', 'U') IS NOT NULL
   AND OBJECT_ID('seguridad.UsuarioUnidadGestionEstado', 'U') IS NOT NULL
BEGIN
    INSERT INTO seguridad.UsuarioUnidadGestionEstado
        (IdUsuario, IdUnidadEjecutora, IdGestion, IdEstadoPoa, CodigoEstado, FechaHoraRegistro, Usuario)
    SELECT DISTINCT
        UGE.IdUsuario,
        UE.IdUnidadEjecutora,
        UGE.IdGestion,
        UGE.IdEstadoPoa,
        UGE.CodigoEstado,
        UGE.FechaHoraRegistro,
        UGE.Usuario
    FROM seguridad.UsuarioDaGestionEstado UGE
    INNER JOIN UnidadesEjecutoras UE ON UE.IdDa = UGE.IdDa
        AND UE.CodigoEstado <> 'E'
    WHERE NOT EXISTS (
        SELECT 1
        FROM seguridad.UsuarioUnidadGestionEstado X
        WHERE X.IdUsuario = UGE.IdUsuario
          AND X.IdUnidadEjecutora = UE.IdUnidadEjecutora
          AND X.IdGestion = UGE.IdGestion
          AND X.IdEstadoPoa = UGE.IdEstadoPoa
          AND X.CodigoEstado <> 'E'
    );
END
GO

IF OBJECT_ID('seguridad.UsuarioUnidadMenuPermiso', 'U') IS NULL
BEGIN
    CREATE TABLE seguridad.UsuarioUnidadMenuPermiso
    (
        IdUsuarioUnidadMenuPermiso uniqueidentifier DEFAULT NEWSEQUENTIALID() PRIMARY KEY,
        IdUsuario uniqueidentifier NOT NULL,
        IdUnidadEjecutora uniqueidentifier NOT NULL,
        IdMenu uniqueidentifier NOT NULL,
        IdGestion uniqueidentifier NOT NULL,
        IdEstadoPoa uniqueidentifier NOT NULL,
        PuedeVer int NULL,
        PuedeCrear int NULL,
        PuedeEditar int NULL,
        PuedeEliminar int NULL,
        CodigoEstado char(1) NOT NULL,
        FechaHoraRegistro datetime NOT NULL DEFAULT GETDATE(),
        Usuario uniqueidentifier NOT NULL,
        FOREIGN KEY (IdUsuario) REFERENCES seguridad.Usuarios (IdUsuario),
        FOREIGN KEY (IdUnidadEjecutora) REFERENCES UnidadesEjecutoras (IdUnidadEjecutora),
        FOREIGN KEY (IdMenu) REFERENCES seguridad.Menus (IdMenu),
        FOREIGN KEY (IdGestion) REFERENCES PeiGestion (IdGestion),
        FOREIGN KEY (IdEstadoPoa) REFERENCES seguridad.EstadosPoa (IdEstadoPoa),
        FOREIGN KEY (CodigoEstado) REFERENCES Estados (CodigoEstado),
        FOREIGN KEY (Usuario) REFERENCES seguridad.Usuarios (IdUsuario)
    );
END
GO

IF OBJECT_ID('seguridad.UsuarioLlaveMenuPermiso', 'U') IS NOT NULL
   AND OBJECT_ID('seguridad.UsuarioUnidadMenuPermiso', 'U') IS NOT NULL
BEGIN
    INSERT INTO seguridad.UsuarioUnidadMenuPermiso
        (IdUsuario, IdUnidadEjecutora, IdMenu, IdGestion, IdEstadoPoa,
         PuedeVer, PuedeCrear, PuedeEditar, PuedeEliminar,
         CodigoEstado, FechaHoraRegistro, Usuario)
    SELECT DISTINCT
        P.IdUsuario,
        LP.IdUnidadEjecutora,
        P.IdMenu,
        P.IdGestion,
        P.IdEstadoPoa,
        P.PuedeVer,
        P.PuedeCrear,
        P.PuedeEditar,
        P.PuedeEliminar,
        P.CodigoEstado,
        P.FechaHoraRegistro,
        P.Usuario
    FROM seguridad.UsuarioLlaveMenuPermiso P
    INNER JOIN LlavesPresupuestarias LP ON LP.IdLlavePresupuestaria = P.IdLlavePresupuestaria
    WHERE LP.IdUnidadEjecutora IS NOT NULL
      AND NOT EXISTS (
        SELECT 1
        FROM seguridad.UsuarioUnidadMenuPermiso X
        WHERE X.IdUsuario = P.IdUsuario
          AND X.IdUnidadEjecutora = LP.IdUnidadEjecutora
          AND X.IdMenu = P.IdMenu
          AND X.IdGestion = P.IdGestion
          AND X.IdEstadoPoa = P.IdEstadoPoa
          AND X.CodigoEstado <> 'E'
      );
END
GO

DECLARE @IdModulo uniqueidentifier =
(
    SELECT TOP 1 IdModulo
    FROM seguridad.Modulos
    WHERE CodigoEstado = 'V'
      AND (Nombre LIKE '%Planificacion%' OR RoutePrefix LIKE '%Planificacion%' OR Codigo LIKE '%POA%')
    ORDER BY Orden
);

DECLARE @IdUsuario uniqueidentifier =
(
    SELECT TOP 1 IdUsuario FROM seguridad.Usuarios WHERE CodigoEstado = 'V'
);

IF @IdModulo IS NOT NULL AND @IdUsuario IS NOT NULL
BEGIN
    ;WITH MenusSeed AS (
        SELECT * FROM (VALUES
            ('PEI', 'Pei', 'Planificacion/peis/index', 'fas fa-sitemap', 10),
            ('OBJ_EST', 'Objetivos Estratégicos', 'Planificacion/obj-estrategico/index', 'fas fa-bullseye', 11),
            ('AREA_EST', 'Áreas Estratégicas', 'Planificacion/area-estrategica/index', 'fas fa-layer-group', 12),
            ('POL_EST', 'Políticas Estratégicas', 'Planificacion/politica-estrategica/index', 'fas fa-balance-scale', 13),
            ('ACC_EST', 'Acciones Estratégicas', 'Planificacion/accion-estrategica/index', 'fas fa-tasks', 14),
            ('IND_EST_ACC', 'Asignar Acciones Estratégicas', 'Planificacion/indicador-estrategico-accion/index', 'fas fa-link', 15),
            ('DA', 'Direcciones Administrativas', 'Planificacion/da/index', 'fas fa-building', 20),
            ('UE', 'Unidades Ejecutoras', 'Planificacion/unidad-ejecutora/index', 'fas fa-sitemap', 21),
            ('PROG', 'Programas', 'Planificacion/programa/index', 'fas fa-list', 22),
            ('PROY', 'Proyectos', 'Planificacion/proyecto/index', 'fas fa-project-diagram', 23),
            ('ACT', 'Actividades', 'Planificacion/actividad/index', 'fas fa-clipboard-list', 24),
            ('LLAVE', 'Llaves Presupuestarias', 'Planificacion/llave-presupuestaria/index', 'fas fa-key', 25),
            ('IND_EST', 'Indicadores Estratégicos', 'Planificacion/indicador-estrategico/index', 'fas fa-chart-line', 30),
            ('IND_EST_ANUAL', 'Programación Anual Estratégica', 'Planificacion/indicador-estrategico-programacion-anual/index', 'fas fa-calendar', 31),
            ('IND_EST_TRIM', 'Programación Trimestral Estratégica', 'Planificacion/indicador-estrategico-programacion-trimestral/index', 'fas fa-calendar-alt', 32),
            ('IND_POA', 'Indicadores POA', 'Planificacion/indicador-poa/index', 'fas fa-chart-bar', 40),
            ('IND_POA_ANUAL', 'Programación Anual POA', 'Planificacion/indicador-poa-programacion-anual/index', 'fas fa-calendar', 41),
            ('IND_POA_TRIM', 'Programación Trimestral POA', 'Planificacion/indicador-poa-programacion-trimestral/index', 'fas fa-calendar-alt', 42),
            ('ESTADO_POA', 'Estados POA', 'Planificacion/estado-poa/index', 'fas fa-flag', 50),
            ('GASTO', 'Gastos', 'Planificacion/gasto/index', 'fas fa-file-invoice-dollar', 51),
            ('FUENTE', 'Fuentes', 'Planificacion/fuente/index', 'fas fa-funnel-dollar', 52),
            ('ORG', 'Organismos', 'Planificacion/organismo/index', 'fas fa-university', 53),
            ('PARTIDA', 'Partidas', 'Planificacion/partida/index', 'fas fa-list-ol', 54),
            ('CRONO', 'Cronograma POA', 'Planificacion/cronograma-poa/index', 'fas fa-calendar-alt', 55),
            ('ENVIOS', 'Envíos POA', 'Planificacion/control-envio-poa/index', 'fas fa-paper-plane', 56),
            ('USUARIOS', 'Usuarios', 'Planificacion/usuario-seguridad/index', 'fas fa-users', 57),
            ('PERMISOS', 'Permisos de menú', 'Planificacion/permiso-menu/index', 'fas fa-user-lock', 58),
            ('FORM1', 'Form 1', 'Planificacion/obj-estrategico/index', 'fas fa-file', 61),
            ('FORM2_FODA', 'FODA Institución', 'Planificacion/foda-institucion/index', 'fas fa-university', 62),
            ('FORM2_UNIDAD', 'FODA Unidad', 'Planificacion/foda-unidad/index', 'fas fa-th-large', 63),
            ('FORM3', 'Form 3', 'Planificacion/obj-institucional/index', 'fas fa-file', 64),
            ('FORM4', 'Form 4', 'Planificacion/obj-especifico/index', 'fas fa-file', 65),
            ('FORM5', 'Form 5', 'Planificacion/operacion/index', 'fas fa-tasks', 66),
            ('FORM7', 'Form 7', 'Planificacion/items-catalogados/index?formulario=7', 'fas fa-boxes', 67),
            ('FORM8', 'Form 8', 'Planificacion/items-catalogados/index?formulario=8', 'fas fa-boxes', 68),
            ('FORM9', 'Form 9', 'Planificacion/items-catalogados/index?formulario=9', 'fas fa-boxes', 69),
            ('FORM10', 'Form 10', 'Planificacion/items-descatalogados/index?formulario=10', 'fas fa-box-open', 70),
            ('FORM11', 'Form 11', 'Planificacion/items-descatalogados/index?formulario=11', 'fas fa-box-open', 71),
            ('FORM12', 'Form 12', 'Planificacion/items-descatalogados/index?formulario=12', 'fas fa-box-open', 72),
            ('FORM13', 'Form 13', 'Planificacion/items-descatalogados/index?formulario=13', 'fas fa-box-open', 73),
            ('FORM14', 'Form 14', 'Planificacion/items-descatalogados/index?formulario=14', 'fas fa-box-open', 74),
            ('FORM15_ING', 'Registrar Ingreso', 'Planificacion/ingreso/index', 'fas fa-coins', 75),
            ('FORM15_TECHO', 'Asignar Techo', 'Planificacion/techo-unidad/index', 'fas fa-chart-pie', 76),
            ('ENVIAR_POA', 'Enviar POA', 'Planificacion/enviar-poa/index', 'fas fa-paper-plane', 77)
        ) AS X(CodigoPermiso, Nombre, Ruta, Icono, Orden)
    )
    INSERT INTO seguridad.Menus
        (IdModulo, Nombre, Ruta, Icono, Orden, Visible, CodigoPermiso, CodigoEstado, IdUsuario)
    SELECT
        @IdModulo, S.Nombre, S.Ruta, S.Icono, S.Orden, 1, S.CodigoPermiso, 'V', @IdUsuario
    FROM MenusSeed S
    WHERE NOT EXISTS (
        SELECT 1 FROM seguridad.Menus M
        WHERE M.IdModulo = @IdModulo
          AND M.CodigoPermiso = S.CodigoPermiso
          AND M.CodigoEstado <> 'E'
    );
END
GO

-- Tras validar la migración se pueden dar de baja las tablas antiguas:
-- DROP TABLE seguridad.UsuarioLlaveMenuPermiso;
-- DROP TABLE seguridad.UsuarioDaGestionEstado;
