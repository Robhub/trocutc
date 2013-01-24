CREATE TABLE IF NOT EXISTS cours (
    login character(8) NOT NULL,
    uv character varying(8) NOT NULL,
    type character(1),
    groupe integer,
    jour integer NOT NULL,
    debut integer NOT NULL,
    fin integer,
    frequence character varying(3),
    salle character varying(10),
    PRIMARY KEY (login, uv, jour, debut)
);
CREATE TABLE IF NOT EXISTS etudiant (
    login character(8) NOT NULL,
    semestre character(4),
    nbuv integer,
    nom text,
    prenom text,
    email text,
    designation text,
    nospam integer,
    PRIMARY KEY (login)
);