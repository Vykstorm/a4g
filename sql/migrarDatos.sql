
SELECT CONCAT('INSERT INTO final_categoria VALUES(', id, ', "', nombre, '", "', familia, '");')
FROM final_categoria;

SELECT CONCAT('INSERT INTO final_comentario VALUES(', id, ', "', fecha_post, '", "', texto, '");')
FROM final_comentario;

SELECT CONCAT('INSERT INTO final_usuario VALUES(', id, ', "', nombre, '", "', passwd, '", ', admin, ', "', fecha_registro, '");')
FROM final_usuario;
	
SELECT CONCAT('INSERT INTO final_producto VALUES(', id, ', "', nombre, '", "', descripcion, '", ', precio, ', "', fecha_publicacion, '", ', id_autor, ', ', id_categoria, ', ', eliminado, ');')
FROM final_producto;

SELECT CONCAT('INSERT INTO final_valora VALUES(', id_usuario, ', ', id_producto, ', ', valoracion, ');')
FROM final_valora;


SELECT CONCAT('INSERT INTO final_adquiere VALUES(', id_usuario, ', ', id_producto, ', ', fecha_compra, ');')
FROM final_adquiere;

SELECT CONCAT('INSERT INTO final_comenta VALUES(', id_usuario, ', ', id_producto, ', ', id_comentario, ');')
FROM final_comenta;

SELECT CONCAT('INSERT INTO final_descarga VALUES(', id_usuario, ', ', id_producto, ', "', fecha_descarga, '");')
FROM final_descarga;
