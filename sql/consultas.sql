/* Consultas orientadas para el proceso de autenticación y 
de registro de los usuarios */

/* Obtener un usuario por id o por nombre */
SELECT id, nombre, passwd, admin
FROM usuario
WHERE id = 1;

SELECT id, nombre, passwd, admin
FROM usuario
WHERE nombre = 'victor';

/* inserta un nuevo usuario */
INSERT INTO usuario (nombre, passwd, fecha_registro) VALUES('victor', 'skdklsksdkls', NOW());


/* esta consulta muestra todos los detalles de un usuario (para el perfil) */
SELECT U.fecha_registro, num_publicaciones
FROM 
    (SELECT U.id, COALESCE(COUNT(P.id), 0) num_publicaciones 
    FROM 
        (SELECT id FROM usuario WHERE id=2) U
        LEFT JOIN
        (SELECT id, id_autor FROM producto) P ON U.id = P.id_autor
    GROUP BY U.id) Q
    INNER JOIN 
    usuario U ON Q.id = U.id;

SELECT U.fecha_registro, num_publicaciones
    FROM 
        (SELECT U.id, COALESCE(COUNT(P.id), 0) num_publicaciones 
        FROM 
            (SELECT id FROM usuario WHERE nombre='victor') U
            LEFT JOIN
            (SELECT id, id_autor FROM producto) P ON U.id = P.id_autor
        GROUP BY U.id) Q
        INNER JOIN 
        usuario U ON Q.id = U.id;

/* consultas para buscar productos publicados en la web */

SELECT id, nombre
FROM producto
WHERE id = 1;

SELECT id, nombre
FROM producto 
WHERE (id = 1) AND (eliminado = false);

SELECT P.id, P.nombre, U.nombre autor
FROM 
    (SELECT id, nombre, id_autor FROM producto WHERE (id = 3) AND (eliminado = false)) P 
    INNER JOIN 
    usuario U ON P.id_autor = U.id;

SELECT P.id, P.nombre, U.nombre autor
    FROM 
        (SELECT id, nombre, id_autor FROM producto WHERE (id = 3) AND ((eliminado = false) OR (id IN (SELECT id_producto FROM adquiere WHERE id_usuario = 2)))) P 
        INNER JOIN 
        usuario U ON P.id_autor = U.id;

/* vistas auxiliares */ 

/* esta vista devuelve como resultado la media de la valoración de los productos
(solo aparecen productos valorados por los usuarios, se ignoran los que no han sido
valorados */
CREATE OR REPLACE VIEW _valora AS (
    SELECT id_producto, AVG(valoracion) valoracion
        FROM valora 
        GROUP BY id_producto
);

/* esta vista proyecta las ids de los productos */
CREATE OR REPLACE VIEW ids_productos AS (
    SELECT id FROM producto 
);

/* esta vista tiene como resultado, la valoración de los productos (incluidos aquellos productos
que no han sido valorados por el usuario) Estos últimos tendrán como ratio de valoración 0 */
CREATE OR REPLACE VIEW valoracion_producto AS ( 
    SELECT id, COALESCE(valoracion, 0) valoracion
    FROM
        ids_productos P
        LEFT JOIN 
        _valora Q ON P.id = Q.id_producto
);
        
/* esta vista nos dice cuantas veces se han descargado los productos (solo aparecen productos
que han sido descargados al menos una vez) */
CREATE OR REPLACE VIEW _descarga AS ( 
    SELECT id_producto id, COUNT(*) num_descargas  
    FROM descarga
    GROUP BY id_producto
);

/* igual que la anterior, pero ahora tiene en cuenta todos los productos, descargados
y no descargados. Los productos no descargados, tienen num_descargas = 0
*/
CREATE OR REPLACE VIEW num_descargas_producto AS ( 
    SELECT P.id, COALESCE(num_descargas, 0) num_descargas
    FROM 
        ids_productos P 
        LEFT JOIN 
        _descarga D ON P.id = D.id
);

/* esta consulta devuelve los detalles de un producto */

SELECT descripcion, precio, fecha_publicacion, valoracion, num_descargas, C.id id_categoria, C.nombre nombre_categoria, familia
FROM
    (SELECT id, descripcion, precio, fecha_publicacion, id_categoria 
    FROM producto
    WHERE (id = 3)) P
    INNER JOIN 
    valoracion_producto V ON P.id = V.id
    INNER JOIN 
    categoria C ON P.id_categoria = C.id
    INNER JOIN 
    num_descargas_producto D ON P.id = D.id;


/* esta consulta devuelve información de los productos comprados por el usuario */
SELECT P.id, P.nombre, U.nombre autor, descripcion, precio, fecha_publicacion, valoracion, num_descargas, C.id id_categoria, C.nombre nombre_categoria, familia, fecha_compra 
FROM
    (SELECT id, nombre, descripcion, precio, fecha_publicacion, id_categoria, id_autor
    FROM producto) P
    INNER JOIN 
    (SELECT * FROM adquiere WHERE id_usuario = 3) A ON A.id_producto = P.id 
    INNER JOIN 
    valoracion_producto V ON P.id = V.id
    INNER JOIN 
    categoria C ON P.id_categoria = C.id
    INNER JOIN 
    num_descargas_producto D ON P.id = D.id
    INNER JOIN 
    usuario U ON U.id = P.id_autor;
    

   
    
/* esta consulta devuelve información de los productos publicados por un usuario */
SELECT P.id, P.nombre, descripcion, precio, fecha_publicacion, valoracion, num_descargas, C.id id_categoria, C.nombre nombre_categoria, familia 
FROM
    (SELECT id, nombre, descripcion, precio, fecha_publicacion, id_categoria, id_autor
    FROM producto WHERE id_autor = 3) P
    INNER JOIN 
    valoracion_producto V ON P.id = V.id
    INNER JOIN 
    categoria C ON P.id_categoria = C.id
    INNER JOIN 
    num_descargas_producto D ON P.id = D.id;


/* consultas relacionadas con catálogos de categorías de productos */

/* número de productos en una categoría */
SELECT COUNT(*) num_productos
FROM producto
WHERE id_categoria = 8;

/* obtener listado de categorías de una familia de productos en concreto */
SELECT id, nombre
FROM categoria
WHERE familia = 'Modelos 3D';

/* obtener el número de productos pertenecientes a una categoría, junto con información de la misma */
SELECT COUNT(*) num_productos
FROM producto
WHERE id_categoria = 5;

SELECT C.id, nombre, familia, COALESCE(COUNT(P.id), 0) num_productos
FROM
    (SELECT * FROM categoria WHERE id = 6) C
    LEFT JOIN 
    (SELECT id, id_categoria FROM producto) P ON C.id = P.id_categoria 
GROUP BY C.id, nombre, familia;


/* obtener los productos pertenecientes a una categoría (catálogo de productos).
Ordenandolos por fecha de publicación y limitando el resultado de la consulta a un conjunto de tuplas
(para seleccionar la página) */

SELECT P.id, P.nombre, U.nombre autor
FROM 
    (SELECT id, nombre, id_autor, fecha_publicacion FROM producto WHERE id_categoria = 5) P 
    INNER JOIN 
    usuario U ON P.id_autor = U.id
ORDER BY fecha_publicacion DESC
LIMIT 1, 1;

/* lo mismo que antes pero ordenandolos por ratio de valoración */
SELECT P.id, P.nombre, U.nombre autor
FROM
    (SELECT id, nombre, id_autor FROM producto WHERE id_categoria = 5) P 
    INNER JOIN 
    usuario U ON P.id_autor = U.id
    INNER JOIN
    valoracion_producto V ON P.id = V.id
ORDER BY valoracion DESC
LIMIT 1, 1;

/* igual pero ordenandolos por nombre */
SELECT P.id, P.nombre, U.nombre autor
FROM 
    (SELECT id, nombre, id_autor FROM producto WHERE id_categoria = 5) P 
    INNER JOIN 
    usuario U ON P.id_autor = U.id
ORDER BY P.nombre DESC
LIMIT 1, 1;

/* lo mismo pero por coste */
SELECT P.id, P.nombre, U.nombre autor
FROM 
    (SELECT id, nombre, id_autor, precio FROM producto WHERE id_categoria = 5) P 
    INNER JOIN 
    usuario U ON P.id_autor = U.id
ORDER BY precio ASC
LIMIT 1, 1;

/* esta query obtiene todas las categorías que pertenecen a una familia especificamente */
SELECT id, nombre
FROM categoria
WHERE familia = 'Modelos 3D';

/* esta es igual solo que selecciona aquellas categorías que tienen al menos un producto */
SELECT id, nombre
FROM 
    (SELECT id, nombre
    FROM categoria
    WHERE (familia = 'Modelos 3D')) C
    INNER JOIN
    (SELECT DISTINCT id_categoria FROM producto) P ON P.id_categoria = C.id;


/* esta es la misma que la anterior solo que las categorías se ordenan de mayor a menor en función
del número de productos */

SELECT id, nombre
FROM 
    (SELECT id, nombre
    FROM categoria
    WHERE (familia = 'Modelos 3D')) C
    INNER JOIN
    (SELECT id_categoria, COUNT(*) num_productos FROM producto GROUP BY id_categoria) P ON P.id_categoria = C.id
ORDER BY num_productos DESC;



/* esta consulta devuelve los productos destacados de una familia en concreto de productos */
SELECT P.id, P.nombre nombre, U.nombre autor, valoracion
FROM 
    (SELECT id, nombre, id_autor, id_categoria FROM producto) P
    INNER JOIN 
    (SELECT id FROM categoria WHERE familia = 'Modelos 3D') C ON P.id_categoria = C.id
    INNER JOIN 
    valoracion_producto V ON P.id = V.id
    INNER JOIN
    (SELECT id, nombre FROM usuario) U ON P.id_autor = U.id
ORDER BY valoracion DESC, P.id ASC
LIMIT 3 OFFSET 0;




SELECT P.id, P.nombre, U.nombre autor, descripcion, precio, fecha_publicacion, valoracion, C.id id_categoria, C.nombre nombre_categoria, familia   
FROM
    (SELECT id, nombre, descripcion, precio, fecha_publicacion, id_categoria, id_autor
    FROM producto WHERE id IN (1,2,3,4,5,6,7,8,9,10)) P
    INNER JOIN 
    valoracion_producto V ON P.id = V.id
    INNER JOIN 
    categoria C ON P.id_categoria = C.id
    INNER JOIN 
    (SELECT id, nombre FROM usuario) U ON P.id_autor = U.id
ORDER BY P.id ASC;


/* consulta para registrar una compra */

INSERT INTO adquiere (id_usuario, id_producto, fecha_compra)
VALUES (8, 63, NOW()), (8, 64, NOW());


/* comprobar si un producto está disponible para un usuario (para que pueda descargarlo);
Esta disponible si se cumplen las siguientes condiciones:
- Es el autor del producto
- El producto es gratuito
- El usuario ha adquirido el producto
*/
    (SELECT id
    FROM (SELECT id, id_autor, precio FROM producto) P
    WHERE ((id_autor = 3) OR (precio = 0)) AND (id = 21))
    UNION
    (SELECT id_producto id
    FROM (SELECT id_usuario, id_producto FROM adquiere) A
    WHERE (id_usuario = 3) AND (id_producto = 21))
   
SELECT id
FROM (SELECT * FROM ids_productos) P
WHERE (id = 63) AND
    (
        (id IN
        (SELECT id
        FROM (SELECT id, id_autor, precio FROM producto) Q
        WHERE (Q.id = P.id) AND ((id_autor = 4) OR (precio = 0))
        ))
        OR
        (id IN
        (SELECT id_producto
        FROM (SELECT id_usuario, id_producto FROM adquiere) Q
        WHERE (Q.id_producto = P.id) AND (id_usuario = 4)
        ))
     )


/* consulta para obtener la valoracion de un usuario sobre un producto. Devuelve un
conjunto vacío si el usuario todavía no ha valorado el producto */
SELECT valoracion
FROM valora
WHERE (id_usuario = 3) AND (id_producto = 4);

/* consulta para obtener los comentarios realizados por los usuarios, de un producto, ordenados
del más reciente al menos reciente */
SELECT C.id, texto, fecha_post, U.id id_usuario, nombre, passwd, admin, fecha_registro
FROM 
    (SELECT id_comentario, id_usuario FROM comenta WHERE id_producto = 63) Q
    INNER JOIN 
    comentario C ON (Q.id_comentario = C.id)
    INNER JOIN 
    (SELECT * FROM usuario) U ON (Q.id_usuario = U.id)
ORDER BY fecha_post DESC, C.id ASC;



/* esta consulta se realiza cuando un usuario comenta un producto */
INSERT INTO comentario (fecha_post, texto) VALUES(NOW(), 'hola');
INSERT INTO comenta (id_usuario, id_producto, id_comentario) VALUES(3, );



/* consulta para añadir/actualizar la valoración de un usuario sobre un 
producto 
*/
INSERT INTO valora (id_usuario, id_producto, valoracion)
VALUES (3, 63, 5)
ON DUPLICATE KEY UPDATE 
valoracion = 7;



