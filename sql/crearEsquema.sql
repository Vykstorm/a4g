
/* tabla de categorías */
CREATE TABLE final_categoria ( 
	id                   int UNSIGNED NOT NULL  AUTO_INCREMENT,
	nombre               varchar(32)  NOT NULL  ,
	familia              varchar(16)  NOT NULL  ,
	CONSTRAINT pk_categoria PRIMARY KEY ( id ),
	CONSTRAINT u_categoria UNIQUE ( nombre, familia ) 
 ) engine=InnoDB;

ALTER TABLE final_categoria ADD CONSTRAINT ch_categoria_familia CHECK ( familia IN ('Modelos 3D', 'Texturas', 'HDRI') );

/* tabla para los comentarios */
CREATE TABLE final_comentario ( 
	id                   int UNSIGNED NOT NULL  AUTO_INCREMENT,
	fecha_post           timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	texto                varchar(400)    ,
	CONSTRAINT pk_comentario PRIMARY KEY ( id )
 ) engine=InnoDB;

/* tabla para los usuarios */
CREATE TABLE final_usuario ( 
	id                   int UNSIGNED NOT NULL  AUTO_INCREMENT,
	nombre               varchar(26)  NOT NULL  ,
	passwd               varchar(256)  NOT NULL  ,
	admin                BOOLEAN  NOT NULL DEFAULT 0 ,
	fecha_registro       date  NOT NULL  ,
	CONSTRAINT pk_usuario PRIMARY KEY ( id ),
	CONSTRAINT u_usuario UNIQUE ( nombre ) 
 ) engine=InnoDB;

/* tabla para los productos */
CREATE TABLE final_producto ( 
	id                   int UNSIGNED NOT NULL  AUTO_INCREMENT,
	nombre               varchar(32)  NOT NULL  ,
	descripcion          varchar(400)    ,
	precio               int UNSIGNED NOT NULL  ,
	fecha_publicacion    timestamp  NOT NULL,
	id_autor             int UNSIGNED NOT NULL  ,
	id_categoria         int UNSIGNED NOT NULL  ,
	eliminado            BOOLEAN  NOT NULL DEFAULT 0 ,
	CONSTRAINT pk_producto PRIMARY KEY ( id ),
	CONSTRAINT u_producto UNIQUE ( nombre, id_categoria ) ,
	CONSTRAINT fk_producto_usuario FOREIGN KEY ( id_autor ) REFERENCES final_usuario( id ) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT fk_producto_categoria FOREIGN KEY ( id_categoria ) REFERENCES final_categoria( id ) ON DELETE NO ACTION ON UPDATE NO ACTION
 ) engine=InnoDB;

CREATE INDEX idx_producto ON final_producto ( id_autor );

CREATE INDEX idx_producto_0 ON final_producto ( id_categoria );

/* tabla para almacenar las valoraciones de los usuarios sobre los 
productos */
CREATE TABLE final_valora ( 
	id_usuario           int UNSIGNED NOT NULL  ,
	id_producto          int UNSIGNED NOT NULL  ,
	valoracion           int UNSIGNED   ,
	CONSTRAINT pk_valora PRIMARY KEY ( id_usuario, id_producto ),
	CONSTRAINT fk_valor_usuario FOREIGN KEY ( id_usuario ) REFERENCES final_usuario( id ) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT fk_valor_producto FOREIGN KEY ( id_producto ) REFERENCES final_producto( id ) ON DELETE NO ACTION ON UPDATE NO ACTION
 ) engine=InnoDB;

ALTER TABLE final_valora ADD CONSTRAINT ch_valoracion CHECK ( (valoracion >= 0) && (valoracion <= 10) );

CREATE INDEX idx_valora_usuario ON final_valora ( id_usuario );

CREATE INDEX idx_valora_producto ON final_valora ( id_producto );

/* tabla para almacenar las adquisiciones de productos por parte de los usuarios */
CREATE TABLE final_adquiere ( 
	id_usuario           int UNSIGNED NOT NULL  ,
	id_producto          int UNSIGNED NOT NULL  ,
	fecha_compra         timestamp  NOT NULL ,
	CONSTRAINT pk_adquiere PRIMARY KEY ( id_usuario, id_producto ),
	CONSTRAINT fk_adquiere_usuario FOREIGN KEY ( id_usuario ) REFERENCES final_usuario( id ) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT fk_adquiere_producto FOREIGN KEY ( id_producto ) REFERENCES final_producto( id ) ON DELETE NO ACTION ON UPDATE NO ACTION
 ) engine=InnoDB;

CREATE INDEX idx_adquiere_usuario ON final_adquiere ( id_usuario );

CREATE INDEX idx_adquiere_producto ON final_adquiere ( id_producto );

/* tabla para saber de que usuarios son los comentarios, y hacia que productos van dirigidos */
CREATE TABLE final_comenta ( 
	id_usuario           int UNSIGNED NOT NULL  ,
	id_producto          int UNSIGNED NOT NULL  ,
	id_comentario        int UNSIGNED NOT NULL  ,
	CONSTRAINT pk_comenta PRIMARY KEY ( id_usuario, id_comentario, id_producto ),
	CONSTRAINT fk_comenta_usuario FOREIGN KEY ( id_usuario ) REFERENCES final_usuario( id ) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT fk_comenta_producto FOREIGN KEY ( id_producto ) REFERENCES final_producto( id ) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT fk_comenta_comentario FOREIGN KEY ( id_comentario ) REFERENCES final_comentario( id ) ON DELETE NO ACTION ON UPDATE NO ACTION
 ) engine=InnoDB;

CREATE INDEX idx_comenta_usuario ON final_comenta ( id_usuario );

CREATE INDEX idx_comenta_producto ON final_comenta ( id_producto );

CREATE INDEX idx_comenta_comentario ON final_comenta ( id_comentario );

/* guarda la información sobre quien descarga los productos */
CREATE TABLE final_descarga ( 
	id_usuario           int UNSIGNED NOT NULL  ,
	id_producto          int UNSIGNED NOT NULL  ,
	fecha_descarga       timestamp  NOT NULL ,
	CONSTRAINT pk_descarga PRIMARY KEY ( id_usuario, id_producto ),
	CONSTRAINT fk_descarga_usuario FOREIGN KEY ( id_usuario ) REFERENCES final_usuario( id ) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT fk_descarga_producto FOREIGN KEY ( id_producto ) REFERENCES final_producto( id ) ON DELETE NO ACTION ON UPDATE NO ACTION
 ) engine=InnoDB;

CREATE INDEX idx_descarga_usuario ON final_descarga ( id_usuario );

CREATE INDEX idx_descarga_producto ON final_descarga ( id_producto );


/* vistas auxiliares */

/* esta permite obtener por cada producto, el número de descargas totales */
CREATE VIEW _final_descarga AS
	(SELECT id_producto id, COUNT(*) num_descargas
	FROM final_descarga
	GROUP BY id_producto);

/* es igual que la anterior, solo que para obtener la valoración de los productos */
CREATE VIEW _final_valora AS 
	(SELECT id_producto id, AVG(valoracion) valoracion
	FROM final_valora
	GROUP BY id_producto
	);
	
/* esta vista projecta las ids de los productos */
CREATE VIEW _final_ids_productos AS
	(SELECT id FROM final_producto);
	
/* esta permite obtener el número de descargas por producto, son la salvedad de que, si
un producto no ha sido descargado, aparecerá en el resultado, con num_descargas = 0 */
CREATE VIEW _final_num_descargas_producto AS
	(SELECT P.id, COALESCE(num_descargas, 0) num_descargas
	FROM _final_ids_productos P LEFT JOIN _final_descarga D ON  (P.id = D.id));

/* igual que la anterior, pero para la valoración de los productos */
CREATE VIEW _final_valoracion_producto AS 
	(SELECT P.id, COALESCE(valoracion, 0) valoracion
	FROM _final_ids_productos P LEFT JOIN _final_valora V ON (P.id = V.id)
	);
