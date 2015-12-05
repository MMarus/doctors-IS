-- SQL skript pre predmet IIS
--
-- @author: Filip Ježovica, xjezov01
-- @author: Marek Marušic, xmarus05

SET NAMES utf8;
SET foreign_key_checks = 0; -- for clean

-- zmazanie tabuliek pre opetovne spustanie skriptu
DROP TABLE IF EXISTS Zamestnanec;
DROP TABLE IF EXISTS NavstevaOrdinacie;
DROP TABLE IF EXISTS Faktura;
DROP TABLE IF EXISTS Liek;
DROP TABLE IF EXISTS Poistovna;
DROP TABLE IF EXISTS Pacient;
DROP TABLE IF EXISTS ExternePracovisko;
DROP TABLE IF EXISTS Vykon;
DROP TABLE IF EXISTS Ockovanie;
DROP TABLE IF EXISTS Vysetrenie;
DROP TABLE IF EXISTS Plan;
DROP TABLE IF EXISTS PredpisanyLiek;
DROP TABLE IF EXISTS Odporucenie;
DROP TABLE IF EXISTS PocasNavstevy;
DROP TABLE IF EXISTS VykonMaPlan;


-- *************************************************************************************************************************************
-- **************************************************** VYTVORENIE TABULIEK ************************************************************
-- *************************************************************************************************************************************
CREATE TABLE Zamestnanec (
    ID INT NOT NULL AUTO_INCREMENT,
    uid VARCHAR(30) NOT NULL,
    upx VARCHAR(100) NOT NULL,
    role VARCHAR(10) NULL DEFAULT 'user',
    lang VARCHAR(3) NULL DEFAULT 'SK',

    meno VARCHAR(30) NULL,
    priezvisko VARCHAR(30) NULL,
    adresa VARCHAR(50) NULL,


    chng DATETIME NULL,
		disabled INT NULL DEFAULT 0,
		deleted INT NULL DEFAULT 0,

	PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE NavstevaOrdinacie (
	ID INT NOT NULL AUTO_INCREMENT,
	Datum DATETIME NOT NULL,
	Poznamky VARCHAR(255),
	id_Pacient INT NOT NULL,
  
	
	PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE Faktura (
	ID INT NOT NULL AUTO_INCREMENT,
	Datum_vystavenia DATETIME NOT NULL,
	Suma INT NOT NULL,
	Splatnost DATETIME NOT NULL,
	id_NavstevaOrdinacie INT NOT NULL,
	id_Poistovna INT NOT NULL,

	PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE Liek (
	ID INT NOT NULL AUTO_INCREMENT,
	Nazov VARCHAR(50) NOT NULL,
	Zlozenie VARCHAR(50) NOT NULL,
	Forma_podania VARCHAR(50) NOT NULL,
	Odporucane_davkovanie VARCHAR(50) NOT NULL,
	Popis VARCHAR(255) NOT NULL,

	PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE Plan (
	ID INT NOT NULL AUTO_INCREMENT,
	Planovany_datum DATETIME NOT NULL,
	Poznamky VARCHAR(255),
	id_Pacient INT NOT NULL,
	id_NavstevaOrdinacie INT NULL,

	PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE Poistovna (
	ID INT NOT NULL AUTO_INCREMENT,
	Nazov VARCHAR(50) NOT NULL,
	Adresa VARCHAR(100) NOT NULL,

	PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE Pacient (
	ID INT NOT NULL AUTO_INCREMENT,
	Rodne_cislo VARCHAR(15) NOT NULL,
	Meno VARCHAR(25) NOT NULL,
	Priezvisko VARCHAR(25) NOT NULL,
	Adresa VARCHAR(100) NOT NULL,
	Krvna_skupina VARCHAR(3) NOT NULL,
	Poznamky VARCHAR(255),
	id_Poistovna INT NOT NULL,

	PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- tu je CHECK ICPE
CREATE TABLE ExternePracovisko (
	ID INT NOT NULL AUTO_INCREMENT,
	Nazov VARCHAR(30) NOT NULL,
	Adresa VARCHAR(100) NOT NULL,
 	Specializacia VARCHAR(50) NOT NULL,
 	Lekar VARCHAR(50) NOT NULL,
  	ICPE INT NOT NULL,

  	PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE Vykon (
	ID INT NOT NULL AUTO_INCREMENT,
	Nazov VARCHAR(30) NOT NULL,
	Popis VARCHAR(255) NOT NULL,
  kontorla INT DEFAULT '0',

	PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- DEGENERALIZACIA
CREATE TABLE Ockovanie (
	ID INT NOT NULL, -- AUTO_INCREMENT,
	Doba_ucinku INT NOT NULL,
	Vyrobca VARCHAR(30) NOT NULL

	-- PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE Vysetrenie (
	ID INT NOT NULL, -- AUTO_INCREMENT,
	Casova_narocnost INT NOT NULL

	-- PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
-- END DEGENERALIZACIA

CREATE TABLE PredpisanyLiek (
    ID INT NOT NULL AUTO_INCREMENT,
	PocetBaleni INT NOT NULL,
	Davkovanie VARCHAR(50) NOT NULL,
	id_NavstevaOrdinacie INT NOT NULL,
	id_Liek INT NOT NULL,

	KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE Odporucenie (
    ID INT NOT NULL AUTO_INCREMENT,
    id_NavstevaOrdinacie INT NOT NULL,
    id_ExternePracovisko INT NOT NULL,

    KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE PocasNavstevy (
    ID INT NOT NULL AUTO_INCREMENT,
    id_NavstevaOrdinacie INT NOT NULL,
    id_Vykon INT NOT NULL,

    KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE VykonMaPlan (
    ID INT NOT NULL AUTO_INCREMENT,
    id_Vykon INT NOT NULL,
    id_Plan INT NOT NULL,

    KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;




-- *************************************************************************************************************************************
-- ******************************************************** KLUCE **********************************************************************
-- *************************************************************************************************************************************

-- Pimarne kluce pre N:N
ALTER TABLE Odporucenie ADD CONSTRAINT PK_Odporucenie PRIMARY KEY (id_NavstevaOrdinacie, id_ExternePracovisko);
ALTER TABLE PredpisanyLiek ADD CONSTRAINT PK_PredpisanyLiek PRIMARY KEY (id_NavstevaOrdinacie, id_Liek);
ALTER TABLE PocasNavstevy ADD CONSTRAINT PK_PocasNavstevy PRIMARY KEY (id_NavstevaOrdinacie, id_Vykon);
ALTER TABLE VykonMaPlan ADD CONSTRAINT PK_VykonMaPlan PRIMARY KEY (id_Vykon, id_Plan);


-- *************** FOREIGN kluce **************************
-- Pridanie Vztahov 1 : N
ALTER TABLE Faktura ADD CONSTRAINT FK_NavstevaOrdinacie FOREIGN KEY(id_NavstevaOrdinacie) REFERENCES NavstevaOrdinacie(ID);    -- 0..n
ALTER TABLE Faktura ADD CONSTRAINT FK_Poistovna FOREIGN KEY(id_Poistovna) REFERENCES Poistovna(ID);                            -- 0..n
ALTER TABLE Pacient ADD CONSTRAINT FK_Poistovna2 FOREIGN KEY(id_Poistovna) REFERENCES Poistovna(ID);                           -- 0..n
ALTER TABLE NavstevaOrdinacie ADD CONSTRAINT FK_Pacient FOREIGN KEY(id_Pacient) REFERENCES Pacient(ID);                        -- 1..n
ALTER TABLE Plan ADD CONSTRAINT FK_Pacient2 FOREIGN KEY(id_Pacient) REFERENCES Pacient(ID);                                    -- 1..n


-- N:N
-- Odporucenie tabulka
ALTER TABLE Odporucenie ADD CONSTRAINT FK_NavstevaOrdinacie3 FOREIGN KEY(id_NavstevaOrdinacie) REFERENCES NavstevaOrdinacie(ID) ON DELETE CASCADE;
ALTER TABLE Odporucenie ADD CONSTRAINT FK_ExternePracovisko FOREIGN KEY(id_ExternePracovisko) REFERENCES ExternePracovisko(ID) ON DELETE CASCADE;
-- PredpisanyLiek tabulka
ALTER TABLE PredpisanyLiek ADD CONSTRAINT FK_NavstevaOrdinacie4 FOREIGN KEY(id_NavstevaOrdinacie) REFERENCES NavstevaOrdinacie(ID) ON DELETE CASCADE;
ALTER TABLE PredpisanyLiek ADD CONSTRAINT FK_Liek FOREIGN KEY(id_Liek) REFERENCES Liek(ID) ON DELETE CASCADE;
-- PocasNavstevy tabulka
ALTER TABLE PocasNavstevy ADD CONSTRAINT FK_NavstevaOrdinacie5 FOREIGN KEY(id_NavstevaOrdinacie) REFERENCES NavstevaOrdinacie(ID) ON DELETE CASCADE;
ALTER TABLE PocasNavstevy ADD CONSTRAINT FK_Vykon FOREIGN KEY(id_Vykon) REFERENCES Vykon(ID) ON DELETE CASCADE;
-- VykonMaPlan tabulka
ALTER TABLE VykonMaPlan ADD CONSTRAINT FK_Plan FOREIGN KEY(id_Plan) REFERENCES Plan(ID) ON DELETE CASCADE;
ALTER TABLE VykonMaPlan ADD CONSTRAINT FK_Vykon2 FOREIGN KEY(id_Vykon) REFERENCES Vykon(ID) ON DELETE CASCADE;

-- DEGENERALIZACIA
ALTER TABLE Ockovanie ADD CONSTRAINT FK_Vykon3 FOREIGN KEY(ID) REFERENCES Vykon(ID) ON DELETE CASCADE;
ALTER TABLE Vysetrenie ADD CONSTRAINT FK_Vykon4 FOREIGN KEY(ID) REFERENCES Vykon(ID) ON DELETE CASCADE;




-- *************************************************************************************************************************************
-- ****************************************************** INSERTY **********************************************************************
-- *************************************************************************************************************************************
-- Zamestnanci
INSERT INTO Zamestnanec (uid,upx,role,lang,chng,meno,priezvisko,adresa) VALUES
('admin','$2y$10$KUjGlc7QFaYXi8N0z6hkT.JtSo/Knp/7iuIpNX9BM89wIcBIWfnNG','admin','SK',CURRENT_TIMESTAMP,'Meno1','Priezvisko1','Adresa 1'),
('0','$2y$10$GSlim4PHnbiAKz7aFz8lZuqJpXKLFnd/0RYlWf2OlEYBG4ev92mFK','doktor','SK',CURRENT_TIMESTAMP,'Meno2','Priezvisko2','Adresa 2'),
('1','$2y$10$AoAfAjARyVhBIT0ebJlvN.y96Gke2mq0HgFGP3JWk8mMVDVKRwpfS','sestra','SK',CURRENT_TIMESTAMP,'Meno3','Priezvisko3','Adresa 3');



-- Poistovne
INSERT INTO Poistovna (Nazov,Adresa) VALUES
('Vseobecna poistovna', '612000 Brno'),
('INA poistovna', '112000 Praha'),
('Materializovana poistovna', '64000 Strání'),
('Velka poistovna', '42000 Brno'),
('MALA poistovna', '42000 Brno');

-- Pracoviska
INSERT INTO ExternePracovisko  (Nazov,Adresa,Specializacia,Lekar,ICPE) VALUES
( 'Nemocnica v Brne' , 'Semilasso, 11111 Brno' , 'Ocne oddelenie' , 'MuDr. Jaro Jaroslav', '10023612'),
( 'NsP NMNV' , 'Nemocnicna 21, 99999 Nove Mesto nad Vahom' , 'Krcne oddelenie' , 'MuDr. Jan Janíček', '10023612'),
( 'Nemocnica v Brne' , 'Ulice 33 55555 Uherske hradiste' , 'Kardio oddelenie' , 'MuDr. Peter Petríček', '10023612'),
( 'NsP v Brode' , 'Ulice 34 55555 Uherske hradiste' , 'Ušné oddelenie' , 'MuDr. Juraj Jurkový', '10023612'),
( 'Poliklinika a.s.' , 'Ulica 12 333333 Bratislava' , 'Úrazove oddelenie' , 'MuDr. Aneta Anetová', '10023612');

-- Lieky (AUTOINCREMENT)
INSERT INTO Liek (Nazov,Zlozenie,Forma_podania,Odporucane_davkovanie,Popis) VALUES
( 'Penicilin 500mg', 'latka1 15mg, latka2 20mg' , 'oralne' , '1tbl/8h', 'Nejaky popis'),
( 'Ibalgin 500mg', 'latka1 20mg, latka2 20mg' , 'oralne' , '2tbl/4h', 'Nejaky popis2'),
( 'Brufen 250', 'latka1 1mg, latka2 200mg' , 'oralne' , '20ml/5h', 'Nejaky popis3'),
( 'Nasivin 15', 'latka16 1mg, latka2 3mg' , 'nasalne' , '2x/8h', 'Nejaky popis4'),
( 'Nasivin 25', 'latka51 15mg, latka2 3mg' , 'nasalne' , '2x/8h', 'Nejaky popis5'),
( 'Liecivo1', 'latka15 12mg, latka2 3mg' , 'oralne' , '2x/8h', 'Nejaky popis1'),
( 'Liecivo2', 'latka21 51mg, latka2 3mg' , 'nasalne' , '2x/5h', 'Nejaky popis2'),
( 'Liecivo3', 'latka21 51mg, latka2 3mg' , 'oralne' , '3x/14h', 'Nejaky popis3'),
( 'Liecivo4', 'latka21 51mg, latka2 3mg' , 'nasalne' , '2x/2h', 'Nejaky popis4'),
( 'Liecivo5', 'latka21 51mg, latka2 3mg' , 'oralne' , '4x/3h', 'Nejaky popis5'),
( 'Liecivo6', 'latka15 12mg, latka2 3mg' , 'oralne' , '2x/5h', 'Nejaky popis6'),
( 'Liecivo7', 'latka21 51mg, latka2 3mg' , 'oralne' , '2x/7h', 'Nejaky popis7'),
( 'Liecivo8', 'latka21 51mg, latka2 3mg' , 'nasalne' , '5x/1h', 'Nejaky popis8'),
( 'Liecivo9', 'latka21 51mg, latka2 3mg' , 'nasalne' , '2x/2h', 'Nejaky popis9'),
( 'Liecivo10', 'latka21 51mg, latka2 3mg' , 'nasalne' , '2x/8h', 'Nejaky popis10');

-- Vykon
INSERT INTO Vykon (Nazov,Popis) VALUES
( 'Prehliadka', 'Pravidelna.'),
( 'Meranie tlaku', 'meranie tlakovym digitalnym pristrojom'),
( 'Odber krvi', 'Iihla 2mm.'),
( 'Ockovanie TBC', 'Iihla 1mm.'),
( 'Ockovanie ZLTACKA', 'Davka c.1');

-- Ockovanie (prve je FK pre Ockovanie) preratane na mesiace
INSERT INTO Ockovanie (ID,Doba_ucinku,Vyrobca) VALUES
( '4', '6', 'VAKCINOTROPIL'),
( '5', '12', 'ZENTIVA');


-- Vysetrenie (prve je FK pre Vysetrenie)  preratane na minuty
INSERT INTO Vysetrenie (ID,Casova_narocnost) VALUES
( '1', '15'),
( '2', '20'),
( '3', '10');

-- potialto constant data




-- Pacienti (maju poistovnu) (AUTOINCREMENT)
INSERT INTO Pacient (Rodne_cislo,Meno,Priezvisko,Adresa,Krvna_skupina,Poznamky,id_Poistovna) VALUES
( '0000001111', 'Filip', 'Jezovica', 'Bozetechova 2, 61200 Brno', 'A+', 'Alergia na Penicilin.', '001'),
( '1111112222', 'Marek', 'Marusic',  'Bozetechova 2, 61200 Brno', 'AB+', 'Alergia na Ibalgin.', '002'),
( '2222223333', 'Nikto', 'Niktos',  'Bozetechova 1, 61200 Brno', '0-', 'Bez poznamky.', '002'),
( '3333334444', 'Volakto', 'Nejaky',  'Bozetechova 1, 61200 Brno', 'B+', 'Bez poznamky.', '002');

-- NavstevaOrdinacie (ma pacienta) (AUTOINCREMENT)
INSERT INTO NavstevaOrdinacie (Datum,Poznamky,id_Pacient) VALUES
( '2015-05-05 15:00:00', 'Vsetko prebehlo ok.', '001'),
( '2015-05-05 16:00:00', 'Toto bol novy pacient, novo registrovany.', '003'),
( '2015-05-05 16:00:00', 'Bez popisu', '003'),
( '2015-05-05 17:00:00', 'abcdcdcsdvgs', '004');

-- Plany (nakonci PACIENT)
INSERT INTO Plan (Planovany_datum,Poznamky,id_Pacient) VALUES
( '2015-11-24 15:00:00' , 'Priniest karticku poistenca.' , '001'),
( '2015-11-25 14:00:00' , 'ajajajjj' , '002'),
( '2015-11-25 16:00:00' , 'volaco' , '003'),
( '2015-11-25 17:00:00' , 'popit volaco' , '004'),
( '2015-11-26 17:00:00' , 'Nejaka poznamka.' , '001');

-- Faktury (ma : Navstevu ordinacie, Poistovnu)
INSERT INTO Faktura (Datum_vystavenia,Suma,Splatnost,id_NavstevaOrdinacie,id_Poistovna) VALUES
( '2015-05-05 15:00:00', '50', '2015-06-05 15:00:00', '001', '001'),
( '2015-05-05 16:00:00', '20', '2015-06-05 15:00:00', '002', '002'),
( '2015-05-05 17:00:00', '300', '2015-06-05 15:00:00', '003', '002');







-- PredpisanyLiek (002 - NAVSTEVA , 001 - LIEK) ***************************************** OPTIMALIZACIA ***************************
INSERT INTO PredpisanyLiek (PocetBaleni,Davkovanie,id_NavstevaOrdinacie,id_Liek) VALUES
( '2', '1tbl/8h', '001','1' ),
( '2', '1tbl/8h', '001','2' ),
( '2', '1tbl/8h', '001','3' ),
( '2', '1tbl/8h', '001','4' ),
( '2', '1tbl/8h', '001','5' ),
( '2', '1tbl/8h', '001','6' ),
( '2', '1tbl/8h', '001','7' ),
( '2', '1tbl/8h', '001','8' ),
 
( '2', '1tbl/8h', '002','1' ),
( '2', '1tbl/8h', '002','2' ),
( '2', '1tbl/8h', '002','3' ),
( '2', '1tbl/8h', '002','4' ),
( '2', '1tbl/8h', '002','5' ),
( '2', '1tbl/8h', '002','6' ),
( '2', '1tbl/8h', '002','7' ),
( '2', '1tbl/8h', '002','8' ),
 
( '2', '1tbl/8h', '003','1' ),
( '2', '1tbl/8h', '003','2' ),
( '2', '1tbl/8h', '003','3' ),
( '2', '1tbl/8h', '003','4' ),
( '2', '1tbl/8h', '003','5' ),
( '2', '1tbl/8h', '003','6' ),
( '2', '1tbl/8h', '003','7' ),
( '2', '1tbl/8h', '003','8' );


-- Odporucenie (ID,  NAVSTEVA,  EXTERNE PRACOVISKO)
INSERT INTO Odporucenie (id_NavstevaOrdinacie,id_ExternePracovisko) VALUES
( '001' , '004'),
( '001' , '002'),
( '003' , '001');

-- PocasNavstevy (ID,  NAVSTEVA, VYKON)
INSERT INTO PocasNavstevy (id_NavstevaOrdinacie,id_Vykon) VALUES
( '002', '001'),
( '002', '002'),
( '001', '003');

-- VykonMaPlan (ID, VYKON, PLAN)
INSERT INTO VykonMaPlan (id_Vykon,id_Plan) VALUES
( '001', '001'),
( '002', '001'),
( '003', '002'),
( '005', '002'),
( '003', '001');
