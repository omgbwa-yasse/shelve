-- ############################################################
-- IntelliGID - Schéma de base de données (MySQL)
-- Généré à partir de intelligid.war (ScriptChargementInitial-Demo.sql)
-- 150 tables - DDL uniquement (structure)
-- Source : com.doculibre.intelligid.entites (mappé par Hibernate .hbm.xml)
-- ############################################################

/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE IF NOT EXISTS `intelligid` DEFAULT CHARACTER SET latin1;
USE `intelligid`;
CREATE TABLE `ActiviteJournalisee` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `nomUtilisateur` varchar(255) DEFAULT NULL,
  `idUniteAdministrative` bigint(20) DEFAULT NULL,
  `codeUniteAdministrative` varchar(255) DEFAULT NULL,
  `codeRoleUtilisateur` varchar(255) DEFAULT NULL,
  `idDossier` bigint(20) DEFAULT NULL,
  `idDocument` bigint(20) DEFAULT NULL,
  `idFichierElectronique` bigint(20) DEFAULT NULL,
  `idTache` bigint(20) DEFAULT NULL,
  `idContenant` bigint(20) DEFAULT NULL,
  `idUtilisateurModifie` bigint(20) DEFAULT NULL,
  `courriel` bit(1) DEFAULT NULL,
  `nomFichierElectroniqueDefaut` varchar(255) DEFAULT NULL,
  `descriptionCourte` varchar(255) DEFAULT NULL,
  `detailsOperation` varchar(4000) DEFAULT NULL,
  `dateHeureActivite` datetime DEFAULT NULL,
  `adresseIP` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idUtilisateurModifie` (`idUtilisateurModifie`),
  KEY `nomUtilisateur` (`nomUtilisateur`),
  KEY `idUniteAdministrative` (`idUniteAdministrative`),
  KEY `idTache` (`idTache`),
  KEY `idFichierElectronique` (`idFichierElectronique`),
  KEY `type` (`type`),
  KEY `idDossier` (`idDossier`),
  KEY `idDocument` (`idDocument`),
  KEY `idContenant` (`idContenant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `AdresseEmplacementPhysique` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `texte` varchar(255) DEFAULT NULL,
  `id_masqueSaisie` bigint(20) DEFAULT NULL,
  `id_ficheDossier` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1AC571181679C751` (`id_masqueSaisie`),
  KEY `FK1AC57118AFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FK1AC57118AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FK1AC571181679C751` FOREIGN KEY (`id_masqueSaisie`) REFERENCES `MasqueSaisieLocalisation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `AutreTitreFicheDocument` (
  `id_ficheDocument` bigint(20) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  KEY `FK2556D179113B0443` (`id_ficheDocument`),
  CONSTRAINT `FK2556D179113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Boite` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `statut` varchar(255) DEFAULT NULL,
  `numero` varchar(255) DEFAULT NULL,
  `nomUniteAdministrative` varchar(255) DEFAULT NULL,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `destruction` datetime DEFAULT NULL,
  `destructionPrevue` datetime DEFAULT NULL,
  `archivagePrevu` datetime DEFAULT NULL,
  `triPrevu` datetime DEFAULT NULL,
  `regleConservation` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `CalendrierConservation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `fondsDocumentaire` bigint(20) DEFAULT NULL,
  `numero` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKEF85010E412A76CB` (`fondsDocumentaire`),
  CONSTRAINT `FKEF85010E412A76CB` FOREIGN KEY (`fondsDocumentaire`) REFERENCES `FondsDocumentaire` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `CategorieOrganisation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK17316B911A2C11D2` (`id_elementParent`),
  KEY `FK17316B91A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK17316B91A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FK17316B911A2C11D2` FOREIGN KEY (`id_elementParent`) REFERENCES `CategorieOrganisation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ClassifDomaineObjFicheDossier` (
  `id_ficheDossier` bigint(20) NOT NULL,
  `id_classificationDomaineObjet` bigint(20) NOT NULL,
  KEY `FKEA9CB21944408FFB` (`id_classificationDomaineObjet`),
  KEY `FKEA9CB219AFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FKEA9CB219AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FKEA9CB21944408FFB` FOREIGN KEY (`id_classificationDomaineObjet`) REFERENCES `ClassificationDomaineObjet` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ClassificationDomaineObjet` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK33598B6BFDFD818E` (`id_elementParent`),
  KEY `FK33598B6BA44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK33598B6BA44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FK33598B6BFDFD818E` FOREIGN KEY (`id_elementParent`) REFERENCES `ClassificationDomaineObjet` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `CollaborateurFicheDocReference` (
  `id_ficheDocumentReference` bigint(20) NOT NULL,
  `collaborateur` varchar(255) DEFAULT NULL,
  KEY `FK94741071E5FB3426` (`id_ficheDocumentReference`),
  CONSTRAINT `FK94741071E5FB3426` FOREIGN KEY (`id_ficheDocumentReference`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Commentaire` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `texte` varchar(4000) DEFAULT NULL,
  `dateCommentaire` datetime DEFAULT NULL,
  `id_utilisateur` bigint(20) DEFAULT NULL,
  `id_tache` bigint(20) DEFAULT NULL,
  `id_listeDeclassement` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKE0D0FF1A67E8AF73` (`id_utilisateur`),
  KEY `FKE0D0FF1AAFB93BFB` (`id_tache`),
  KEY `FKE0D0FF1ABB4D5949` (`id_listeDeclassement`),
  CONSTRAINT `FKE0D0FF1ABB4D5949` FOREIGN KEY (`id_listeDeclassement`) REFERENCES `ListeDeclassement` (`id`),
  CONSTRAINT `FKE0D0FF1A67E8AF73` FOREIGN KEY (`id_utilisateur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKE0D0FF1AAFB93BFB` FOREIGN KEY (`id_tache`) REFERENCES `Tache` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Competence` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK4B6A73E9A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK4B6A73E9A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ConfigurationGlobale` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Contact` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `nomPosteClassement` varchar(255) DEFAULT NULL,
  `id_organisation` bigint(20) DEFAULT NULL,
  `id_typeContact` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK9BEFBC00E388FA3F` (`id_organisation`),
  KEY `FK9BEFBC00FD19C467` (`id_typeContact`),
  CONSTRAINT `FK9BEFBC00FD19C467` FOREIGN KEY (`id_typeContact`) REFERENCES `TypeContact` (`id`),
  CONSTRAINT `FK9BEFBC00E388FA3F` FOREIGN KEY (`id_organisation`) REFERENCES `Organisation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Contenant` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `identifiant` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `dateEmprunt` datetime DEFAULT NULL,
  `id_emprunteur` bigint(20) DEFAULT NULL,
  `modifieApresEmprunt` bit(1) DEFAULT NULL,
  `id_emplacement` bigint(20) DEFAULT NULL,
  `id_typeContenant` bigint(20) DEFAULT NULL,
  `identifiantTemporaire` varchar(255) DEFAULT NULL,
  `dateCreationHorodatee` datetime DEFAULT NULL,
  `dateModificationHorodatee` datetime DEFAULT NULL,
  `ficheCompletee` bit(1) DEFAULT NULL,
  `id_utilisateurModificateur` bigint(20) DEFAULT NULL,
  `id_utilisateurSoumetteur` bigint(20) DEFAULT NULL,
  `datePrevueRetourEmprunt` datetime DEFAULT NULL,
  `resume` varchar(4000) DEFAULT NULL,
  `valeurTri` varchar(255) DEFAULT NULL,
  `typeDeclassement` varchar(255) DEFAULT NULL,
  `dateFinalisation` datetime DEFAULT NULL,
  `plein` bit(1) DEFAULT NULL,
  `dateRetourEmpruntReelle` datetime DEFAULT NULL,
  `dateTransfertReelle` datetime DEFAULT NULL,
  `dateVersementReelle` datetime DEFAULT NULL,
  `id_posteClassement` bigint(20) DEFAULT NULL,
  `dateDernierRappel` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifiant` (`identifiant`),
  UNIQUE KEY `identifiantTemporaire` (`identifiantTemporaire`),
  KEY `FK5F2DE08CB164CEB8` (`id_emplacement`),
  KEY `FK5F2DE08C1E348FBF` (`id_typeContenant`),
  KEY `FK5F2DE08C47D2C2C9` (`id_emprunteur`),
  KEY `FK5F2DE08CB4F4DC49` (`id_utilisateurModificateur`),
  KEY `FK5F2DE08CFACF7EF2` (`id_posteClassement`),
  KEY `FK5F2DE08C982A95A4` (`id_utilisateurSoumetteur`),
  KEY `valeurTriContenant` (`valeurTri`),
  CONSTRAINT `FK5F2DE08C982A95A4` FOREIGN KEY (`id_utilisateurSoumetteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK5F2DE08C1E348FBF` FOREIGN KEY (`id_typeContenant`) REFERENCES `TypeContenant` (`id`),
  CONSTRAINT `FK5F2DE08C47D2C2C9` FOREIGN KEY (`id_emprunteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK5F2DE08CB164CEB8` FOREIGN KEY (`id_emplacement`) REFERENCES `Emplacement` (`id`),
  CONSTRAINT `FK5F2DE08CB4F4DC49` FOREIGN KEY (`id_utilisateurModificateur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK5F2DE08CFACF7EF2` FOREIGN KEY (`id_posteClassement`) REFERENCES `UniteAdministrative` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ContenantListeDeclassement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `id_listeDeclassement` bigint(20) DEFAULT NULL,
  `id_contenant` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK6587AA07CDB1C116` (`id_contenant`),
  KEY `FK6587AA07BB4D5949` (`id_listeDeclassement`),
  CONSTRAINT `FK6587AA07BB4D5949` FOREIGN KEY (`id_listeDeclassement`) REFERENCES `ListeDeclassement` (`id`),
  CONSTRAINT `FK6587AA07CDB1C116` FOREIGN KEY (`id_contenant`) REFERENCES `Contenant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Correspondance` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `numero` varchar(255) DEFAULT NULL,
  `dateCorrespondance` datetime DEFAULT NULL,
  `dateTransmission` datetime DEFAULT NULL,
  `dateEcheance` datetime DEFAULT NULL,
  `dateReception` datetime DEFAULT NULL,
  `dateFermeture` datetime DEFAULT NULL,
  `objetCorrespondance` varchar(4000) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `remarques` varchar(4000) DEFAULT NULL,
  `id_organisationDestinataire` bigint(20) DEFAULT NULL,
  `id_organisationExpediteur` bigint(20) DEFAULT NULL,
  `id_contactExpediteur` bigint(20) DEFAULT NULL,
  `id_contactDestinataire` bigint(20) DEFAULT NULL,
  `id_utilisateurExpediteur` bigint(20) DEFAULT NULL,
  `id_utilisateurDdestinataire` bigint(20) DEFAULT NULL,
  `id_modeExpeditionReception` bigint(20) DEFAULT NULL,
  `id_typeAccuseReception` bigint(20) DEFAULT NULL,
  `id_statutCorrespondance` bigint(20) DEFAULT NULL,
  `id_typeCorrespondance` bigint(20) DEFAULT NULL,
  `id_posteClassement` bigint(20) DEFAULT NULL,
  `id_ficheDossier` bigint(20) DEFAULT NULL,
  `id_organisation` bigint(20) DEFAULT NULL,
  `id_utilisateurDestinataire` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK6241AA3EB694559` (`id_typeAccuseReception`),
  KEY `FK6241AA3E5E927D51` (`id_modeExpeditionReception`),
  KEY `FK6241AA3EFACF7EF2` (`id_posteClassement`),
  KEY `FK6241AA3E8BC19E08` (`id_utilisateurDestinataire`),
  KEY `FK6241AA3E2EF49635` (`id_typeCorrespondance`),
  KEY `FK6241AA3E9239A2E7` (`id_statutCorrespondance`),
  KEY `FK6241AA3E5D50B48E` (`id_utilisateurDdestinataire`),
  KEY `FK6241AA3EC1A6B76E` (`id_utilisateurExpediteur`),
  KEY `FK6241AA3E9E24E77A` (`id_organisationExpediteur`),
  KEY `FK6241AA3EC5D5CFEA` (`id_contactDestinataire`),
  KEY `FK6241AA3EE388FA3F` (`id_organisation`),
  KEY `FK6241AA3EAFBAA5E9` (`id_ficheDossier`),
  KEY `FK6241AA3EE1790C90` (`id_contactExpediteur`),
  KEY `FK6241AA3E796B3254` (`id_organisationDestinataire`),
  CONSTRAINT `FK6241AA3E796B3254` FOREIGN KEY (`id_organisationDestinataire`) REFERENCES `Organisation` (`id`),
  CONSTRAINT `FK6241AA3E2EF49635` FOREIGN KEY (`id_typeCorrespondance`) REFERENCES `TypeCorrespondance` (`id`),
  CONSTRAINT `FK6241AA3E5D50B48E` FOREIGN KEY (`id_utilisateurDdestinataire`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK6241AA3E5E927D51` FOREIGN KEY (`id_modeExpeditionReception`) REFERENCES `ModeExpeditionReception` (`id`),
  CONSTRAINT `FK6241AA3E8BC19E08` FOREIGN KEY (`id_utilisateurDestinataire`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK6241AA3E9239A2E7` FOREIGN KEY (`id_statutCorrespondance`) REFERENCES `StatutCorrespondance` (`id`),
  CONSTRAINT `FK6241AA3E9E24E77A` FOREIGN KEY (`id_organisationExpediteur`) REFERENCES `Organisation` (`id`),
  CONSTRAINT `FK6241AA3EAFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FK6241AA3EB694559` FOREIGN KEY (`id_typeAccuseReception`) REFERENCES `TypeAccuseReception` (`id`),
  CONSTRAINT `FK6241AA3EC1A6B76E` FOREIGN KEY (`id_utilisateurExpediteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK6241AA3EC5D5CFEA` FOREIGN KEY (`id_contactDestinataire`) REFERENCES `Contact` (`id`),
  CONSTRAINT `FK6241AA3EE1790C90` FOREIGN KEY (`id_contactExpediteur`) REFERENCES `Contact` (`id`),
  CONSTRAINT `FK6241AA3EE388FA3F` FOREIGN KEY (`id_organisation`) REFERENCES `Organisation` (`id`),
  CONSTRAINT `FK6241AA3EFACF7EF2` FOREIGN KEY (`id_posteClassement`) REFERENCES `UniteAdministrative` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `CreateurFicheDocument` (
  `id_ficheDocument` bigint(20) NOT NULL,
  `createur` varchar(255) DEFAULT NULL,
  KEY `FK2B5820FF113B0443` (`id_ficheDocument`),
  CONSTRAINT `FK2B5820FF113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Delai` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `description` varchar(4000) DEFAULT NULL,
  `numero` varchar(255) DEFAULT NULL,
  `numeroRegle` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `titreSerie` varchar(255) DEFAULT NULL,
  `uaResponsables` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `approuve` bit(1) DEFAULT NULL,
  `recueil` varchar(255) DEFAULT NULL,
  `pageRecueil` varchar(255) DEFAULT NULL,
  `dateApprobation` datetime DEFAULT NULL,
  `dateRetrait` datetime DEFAULT NULL,
  `documentsEssentiels` bit(1) DEFAULT NULL,
  `documentsConfidentiels` bit(1) DEFAULT NULL,
  `referencesJuridiques` varchar(4000) DEFAULT NULL,
  `remarquesGenerales` varchar(4000) DEFAULT NULL,
  `remarquesRelativesDelai` varchar(4000) DEFAULT NULL,
  `id_calendrierConservation` bigint(20) DEFAULT NULL,
  `id_exemplaireSecondaire` bigint(20) DEFAULT NULL,
  `historique` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK3EDC953C2CEF129` (`id_calendrierConservation`),
  KEY `FK3EDC953668AFA0` (`id_exemplaireSecondaire`),
  CONSTRAINT `FK3EDC953668AFA0` FOREIGN KEY (`id_exemplaireSecondaire`) REFERENCES `Exemplaire` (`id`),
  CONSTRAINT `FK3EDC953C2CEF129` FOREIGN KEY (`id_calendrierConservation`) REFERENCES `CalendrierConservation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `DemandeValidListeDeclassement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `dateDemande` datetime DEFAULT NULL,
  `dateValidation` datetime DEFAULT NULL,
  `id_listeDeclassement` bigint(20) DEFAULT NULL,
  `id_expediteur` bigint(20) DEFAULT NULL,
  `id_destinataire` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK6BF40C91D9C9AD73` (`id_expediteur`),
  KEY `FK6BF40C91BB4D5949` (`id_listeDeclassement`),
  KEY `FK6BF40C9126FF26CD` (`id_destinataire`),
  CONSTRAINT `FK6BF40C9126FF26CD` FOREIGN KEY (`id_destinataire`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK6BF40C91BB4D5949` FOREIGN KEY (`id_listeDeclassement`) REFERENCES `ListeDeclassement` (`id`),
  CONSTRAINT `FK6BF40C91D9C9AD73` FOREIGN KEY (`id_expediteur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `DestFicheDocumentTransaction` (
  `id_ficheDocumentTransaction` bigint(20) NOT NULL,
  `destinataire` varchar(255) DEFAULT NULL,
  KEY `FK8D1F776845EE94B9` (`id_ficheDocumentTransaction`),
  CONSTRAINT `FK8D1F776845EE94B9` FOREIGN KEY (`id_ficheDocumentTransaction`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `DestinatairesTache` (
  `id_tache` bigint(20) NOT NULL,
  `id_utilisateur` bigint(20) NOT NULL,
  KEY `FKE1F7FBD567E8AF73` (`id_utilisateur`),
  KEY `FKE1F7FBD5AFB93BFB` (`id_tache`),
  CONSTRAINT `FKE1F7FBD5AFB93BFB` FOREIGN KEY (`id_tache`) REFERENCES `Tache` (`id`),
  CONSTRAINT `FKE1F7FBD567E8AF73` FOREIGN KEY (`id_utilisateur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `DetenteurPrincipalDelai` (
  `id_delai` bigint(20) NOT NULL,
  `id_uniteAdministrative` bigint(20) NOT NULL,
  KEY `FKB007FF2F67EA7BD9` (`id_delai`),
  KEY `FKB007FF2F231F6571` (`id_uniteAdministrative`),
  CONSTRAINT `FKB007FF2F231F6571` FOREIGN KEY (`id_uniteAdministrative`) REFERENCES `UniteAdministrative` (`id`),
  CONSTRAINT `FKB007FF2F67EA7BD9` FOREIGN KEY (`id_delai`) REFERENCES `Delai` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `DomaineValeurs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `pilotableUtilisateur` bit(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ElementDomaineValeurs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKDA566C2DA44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKDA566C2DA44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ElementDVHierarchise` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKA350E9E9F9E722D` (`id_elementParent`),
  KEY `FKA350E9E9A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKA350E9E9A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FKA350E9E9F9E722D` FOREIGN KEY (`id_elementParent`) REFERENCES `ElementDVHierarchise` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Emplacement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `id_emplacementParent` bigint(20) DEFAULT NULL,
  `dateCreationHorodatee` datetime DEFAULT NULL,
  `dateModificationHorodatee` datetime DEFAULT NULL,
  `id_utilisateurModificateur` bigint(20) DEFAULT NULL,
  `id_utilisateurSoumetteur` bigint(20) DEFAULT NULL,
  `resume` varchar(4000) DEFAULT NULL,
  `valeurTri` varchar(255) DEFAULT NULL,
  `capacite` int(11) DEFAULT NULL,
  `typeDeclassement` varchar(255) DEFAULT NULL,
  `id_typeEmplacement` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK8FC76B1D964D20A2` (`id_emplacementParent`),
  KEY `FK8FC76B1DB4F4DC49` (`id_utilisateurModificateur`),
  KEY `FK8FC76B1DEC6E9721` (`id_typeEmplacement`),
  KEY `FK8FC76B1D982A95A4` (`id_utilisateurSoumetteur`),
  KEY `valeurTriEmplacement` (`valeurTri`),
  CONSTRAINT `FK8FC76B1D982A95A4` FOREIGN KEY (`id_utilisateurSoumetteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK8FC76B1D964D20A2` FOREIGN KEY (`id_emplacementParent`) REFERENCES `Emplacement` (`id`),
  CONSTRAINT `FK8FC76B1DB4F4DC49` FOREIGN KEY (`id_utilisateurModificateur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK8FC76B1DEC6E9721` FOREIGN KEY (`id_typeEmplacement`) REFERENCES `TypeEmplacement` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Exemplaire` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `numero` varchar(255) DEFAULT NULL,
  `principal` bit(1) DEFAULT NULL,
  `id_delai` bigint(20) DEFAULT NULL,
  `id_statutActif` bigint(20) DEFAULT NULL,
  `id_statutInactif` bigint(20) DEFAULT NULL,
  `id_statutSemiActif` bigint(20) DEFAULT NULL,
  `remarqueSupport` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_statutActif` (`id_statutActif`),
  UNIQUE KEY `id_statutInactif` (`id_statutInactif`),
  UNIQUE KEY `id_statutSemiActif` (`id_statutSemiActif`),
  KEY `FK9EFBD4D267EA7BD9` (`id_delai`),
  KEY `FK9EFBD4D26B766B36` (`id_statutInactif`),
  KEY `FK9EFBD4D2F1FEB04D` (`id_statutSemiActif`),
  KEY `FK9EFBD4D26C1A977B` (`id_statutActif`),
  CONSTRAINT `FK9EFBD4D26C1A977B` FOREIGN KEY (`id_statutActif`) REFERENCES `StatutArchivistique` (`id`),
  CONSTRAINT `FK9EFBD4D267EA7BD9` FOREIGN KEY (`id_delai`) REFERENCES `Delai` (`id`),
  CONSTRAINT `FK9EFBD4D26B766B36` FOREIGN KEY (`id_statutInactif`) REFERENCES `StatutArchivistique` (`id`),
  CONSTRAINT `FK9EFBD4D2F1FEB04D` FOREIGN KEY (`id_statutSemiActif`) REFERENCES `StatutArchivistique` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `FicheDocument` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `encryptionKey` tinyblob,
  `courriel` bit(1) DEFAULT NULL,
  `nomFichierElectroniqueDefaut` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `dateCreation` datetime DEFAULT NULL,
  `dateCreationHorodatee` datetime DEFAULT NULL,
  `dateModificationHorodatee` datetime DEFAULT NULL,
  `ficheCompletee` bit(1) DEFAULT NULL,
  `approbationRequise` bit(1) DEFAULT NULL,
  `resume` varchar(4000) DEFAULT NULL,
  `tableMatieres` varchar(255) DEFAULT NULL,
  `quantite` varchar(255) DEFAULT NULL,
  `dimension` varchar(255) DEFAULT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `versionFinaleCreation` bit(1) DEFAULT NULL,
  `emplacementAvantAjout` varchar(255) DEFAULT NULL,
  `valeurTri` varchar(255) DEFAULT NULL,
  `dateEmprunt` datetime DEFAULT NULL,
  `id_emprunteur` bigint(20) DEFAULT NULL,
  `modifieApresEmprunt` bit(1) DEFAULT NULL,
  `id_utilisateurModificateur` bigint(20) DEFAULT NULL,
  `id_utilisateurSoumetteur` bigint(20) DEFAULT NULL,
  `id_utilisateurValideur` bigint(20) DEFAULT NULL,
  `id_relationEstPartieDe` bigint(20) DEFAULT NULL,
  `id_competence` bigint(20) DEFAULT NULL,
  `id_scolariteAuditoire` bigint(20) DEFAULT NULL,
  `id_statutAutoriteEnregistr` bigint(20) DEFAULT NULL,
  `id_statutVersion` bigint(20) DEFAULT NULL,
  `id_statutConfidentialite` bigint(20) DEFAULT NULL,
  `id_classificationTypeDocument` bigint(20) DEFAULT NULL,
  `classificationTypeDocumentTemp` varchar(255) DEFAULT NULL,
  `datePublication` datetime DEFAULT NULL,
  `editeur` varchar(255) DEFAULT NULL,
  `dateExpedition` datetime DEFAULT NULL,
  `dateReception` datetime DEFAULT NULL,
  `dateSignature` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKE82EB118744BAEF7` (`id_competence`),
  KEY `FKE82EB11812E3AD65` (`id_statutVersion`),
  KEY `FKE82EB118B4F4DC49` (`id_utilisateurModificateur`),
  KEY `FKE82EB1184A9C0D80` (`id_statutAutoriteEnregistr`),
  KEY `FKE82EB1188D9AEE0D` (`id_relationEstPartieDe`),
  KEY `FKE82EB118982A95A4` (`id_utilisateurSoumetteur`),
  KEY `FKE82EB11815785B9D` (`id_scolariteAuditoire`),
  KEY `FKE82EB1189C8B46D5` (`id_classificationTypeDocument`),
  KEY `FKE82EB1188CFB12D9` (`id_statutConfidentialite`),
  KEY `FKE82EB11881E908B9` (`id_utilisateurValideur`),
  KEY `FKE82EB11847D2C2C9` (`id_emprunteur`),
  KEY `valeurTriFicheDocument` (`valeurTri`),
  CONSTRAINT `FKE82EB11847D2C2C9` FOREIGN KEY (`id_emprunteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKE82EB11812E3AD65` FOREIGN KEY (`id_statutVersion`) REFERENCES `StatutVersion` (`id`),
  CONSTRAINT `FKE82EB11815785B9D` FOREIGN KEY (`id_scolariteAuditoire`) REFERENCES `ScolariteAuditoire` (`id`),
  CONSTRAINT `FKE82EB1184A9C0D80` FOREIGN KEY (`id_statutAutoriteEnregistr`) REFERENCES `StatutAutoriteEnregistrement` (`id`),
  CONSTRAINT `FKE82EB118744BAEF7` FOREIGN KEY (`id_competence`) REFERENCES `Competence` (`id`),
  CONSTRAINT `FKE82EB11881E908B9` FOREIGN KEY (`id_utilisateurValideur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKE82EB1188CFB12D9` FOREIGN KEY (`id_statutConfidentialite`) REFERENCES `StatutConfidentialite` (`id`),
  CONSTRAINT `FKE82EB1188D9AEE0D` FOREIGN KEY (`id_relationEstPartieDe`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FKE82EB118982A95A4` FOREIGN KEY (`id_utilisateurSoumetteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKE82EB1189C8B46D5` FOREIGN KEY (`id_classificationTypeDocument`) REFERENCES `TypeDocument` (`id`),
  CONSTRAINT `FKE82EB118B4F4DC49` FOREIGN KEY (`id_utilisateurModificateur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `FicheDossier` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `encryptionKey` tinyblob,
  `path` varchar(255) DEFAULT NULL,
  `ancienNumeroDossier` varchar(255) DEFAULT NULL,
  `statutArchivistiqueGVAA` varchar(255) DEFAULT NULL,
  `dateCreationHorodatee` datetime DEFAULT NULL,
  `dateModificationHorodatee` datetime DEFAULT NULL,
  `ficheCompletee` bit(1) DEFAULT NULL,
  `resume` varchar(4000) DEFAULT NULL,
  `tableMatieres` varchar(255) DEFAULT NULL,
  `quantite` varchar(255) DEFAULT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `tache` bit(1) DEFAULT NULL,
  `valeurTri` varchar(255) DEFAULT NULL,
  `dateDestructionApprouvee` datetime DEFAULT NULL,
  `dateDestructionReelle` datetime DEFAULT NULL,
  `dateFermeture` datetime DEFAULT NULL,
  `dateOuverture` datetime DEFAULT NULL,
  `dateTraitement` datetime DEFAULT NULL,
  `dateTransfertApprouvee` datetime DEFAULT NULL,
  `dateTransfertReelle` datetime DEFAULT NULL,
  `dateVersementApprouvee` datetime DEFAULT NULL,
  `dateVersementReelle` datetime DEFAULT NULL,
  `restrictionsUtilisation` varchar(255) DEFAULT NULL,
  `statutEssentiel` bit(1) DEFAULT NULL,
  `statutExemplairePrincipalSaisi` bit(1) DEFAULT NULL,
  `ouvertureAnnuelle` bit(1) DEFAULT NULL,
  `numeroBoite` varchar(255) DEFAULT NULL,
  `indisponible` bit(1) DEFAULT NULL,
  `dateEmprunt` datetime DEFAULT NULL,
  `id_emprunteur` bigint(20) DEFAULT NULL,
  `datePrevueRetourEmprunt` datetime DEFAULT NULL,
  `modifieApresEmprunt` bit(1) DEFAULT NULL,
  `id_classificationTypeDossier` bigint(20) DEFAULT NULL,
  `id_utilisateurModificateur` bigint(20) DEFAULT NULL,
  `id_utilisateurSoumetteur` bigint(20) DEFAULT NULL,
  `id_utilisateurValideur` bigint(20) DEFAULT NULL,
  `id_utilDestructionReelle` bigint(20) DEFAULT NULL,
  `id_utilVersementReel` bigint(20) DEFAULT NULL,
  `id_utilTransfertReel` bigint(20) DEFAULT NULL,
  `id_relationEstPartieDe` bigint(20) DEFAULT NULL,
  `id_processusActivite` bigint(20) DEFAULT NULL,
  `id_contenant` bigint(20) DEFAULT NULL,
  `id_UAProprietaire` bigint(20) DEFAULT NULL,
  `id_competence` bigint(20) DEFAULT NULL,
  `id_limiteAcces` bigint(20) DEFAULT NULL,
  `id_regleConservation` bigint(20) DEFAULT NULL,
  `id_scolariteAuditoire` bigint(20) DEFAULT NULL,
  `id_statutAutoriteEnregistr` bigint(20) DEFAULT NULL,
  `id_statutDossier` bigint(20) DEFAULT NULL,
  `id_statutConfidentialite` bigint(20) DEFAULT NULL,
  `approbationRequise` bit(1) DEFAULT NULL,
  `dateDernierRappel` datetime DEFAULT NULL,
  `dateRetourEmpruntReelle` datetime DEFAULT NULL,
  `id_utilisateurFermeture` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKAD872B2EC26F8EDB` (`id_utilVersementReel`),
  KEY `FKAD872B2ECDB1C116` (`id_contenant`),
  KEY `FKAD872B2EB4F4DC49` (`id_utilisateurModificateur`),
  KEY `FKAD872B2E4A9C0D80` (`id_statutAutoriteEnregistr`),
  KEY `FKAD872B2E982A95A4` (`id_utilisateurSoumetteur`),
  KEY `FKAD872B2EC4C0714B` (`id_statutDossier`),
  KEY `FKAD872B2E8CFB12D9` (`id_statutConfidentialite`),
  KEY `FKAD872B2E47D2C2C9` (`id_emprunteur`),
  KEY `FKAD872B2E8AEEC917` (`id_classificationTypeDossier`),
  KEY `FKAD872B2E81E908B9` (`id_utilisateurValideur`),
  KEY `FKAD872B2EF7DDF23` (`id_processusActivite`),
  KEY `FKAD872B2ED7C7F6DA` (`id_UAProprietaire`),
  KEY `FKAD872B2E6908E365` (`id_limiteAcces`),
  KEY `FKAD872B2E744BAEF7` (`id_competence`),
  KEY `FKAD872B2E31BD4667` (`id_regleConservation`),
  KEY `FKAD872B2EEC390DB1` (`id_utilTransfertReel`),
  KEY `FKAD872B2E8D9AEE0D` (`id_relationEstPartieDe`),
  KEY `FKAD872B2E15785B9D` (`id_scolariteAuditoire`),
  KEY `FKAD872B2EB9305D73` (`id_utilDestructionReelle`),
  KEY `valeurTriFicheDossier` (`valeurTri`),
  KEY `FKAD872B2E44720F4` (`id_utilisateurFermeture`),
  CONSTRAINT `FKAD872B2E44720F4` FOREIGN KEY (`id_utilisateurFermeture`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKAD872B2E15785B9D` FOREIGN KEY (`id_scolariteAuditoire`) REFERENCES `ScolariteAuditoire` (`id`),
  CONSTRAINT `FKAD872B2E31BD4667` FOREIGN KEY (`id_regleConservation`) REFERENCES `RegleConservation` (`id`),
  CONSTRAINT `FKAD872B2E47D2C2C9` FOREIGN KEY (`id_emprunteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKAD872B2E4A9C0D80` FOREIGN KEY (`id_statutAutoriteEnregistr`) REFERENCES `StatutAutoriteEnregistrement` (`id`),
  CONSTRAINT `FKAD872B2E6908E365` FOREIGN KEY (`id_limiteAcces`) REFERENCES `LimiteAcces` (`id`),
  CONSTRAINT `FKAD872B2E744BAEF7` FOREIGN KEY (`id_competence`) REFERENCES `Competence` (`id`),
  CONSTRAINT `FKAD872B2E81E908B9` FOREIGN KEY (`id_utilisateurValideur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKAD872B2E8AEEC917` FOREIGN KEY (`id_classificationTypeDossier`) REFERENCES `TypeDossier` (`id`),
  CONSTRAINT `FKAD872B2E8CFB12D9` FOREIGN KEY (`id_statutConfidentialite`) REFERENCES `StatutConfidentialite` (`id`),
  CONSTRAINT `FKAD872B2E8D9AEE0D` FOREIGN KEY (`id_relationEstPartieDe`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FKAD872B2E982A95A4` FOREIGN KEY (`id_utilisateurSoumetteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKAD872B2EB4F4DC49` FOREIGN KEY (`id_utilisateurModificateur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKAD872B2EB9305D73` FOREIGN KEY (`id_utilDestructionReelle`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKAD872B2EC26F8EDB` FOREIGN KEY (`id_utilVersementReel`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKAD872B2EC4C0714B` FOREIGN KEY (`id_statutDossier`) REFERENCES `StatutDossier` (`id`),
  CONSTRAINT `FKAD872B2ECDB1C116` FOREIGN KEY (`id_contenant`) REFERENCES `Contenant` (`id`),
  CONSTRAINT `FKAD872B2ED7C7F6DA` FOREIGN KEY (`id_UAProprietaire`) REFERENCES `UniteAdministrative` (`id`),
  CONSTRAINT `FKAD872B2EEC390DB1` FOREIGN KEY (`id_utilTransfertReel`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKAD872B2EF7DDF23` FOREIGN KEY (`id_processusActivite`) REFERENCES `ProcessusActivite` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `FicheDossierListeDeclassement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `dossierInclus` bit(1) DEFAULT NULL,
  `dateValidation` datetime DEFAULT NULL,
  `supportInformatique` bit(1) DEFAULT NULL,
  `supportAnalogique` bit(1) DEFAULT NULL,
  `id_listeDeclassement` bigint(20) DEFAULT NULL,
  `id_ficheDossier` bigint(20) DEFAULT NULL,
  `id_utilisateurValidation` bigint(20) DEFAULT NULL,
  `id_contenantListeDeclassement` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK3FCB7A5DC04CD2C` (`id_utilisateurValidation`),
  KEY `FK3FCB7A524DBD02B` (`id_contenantListeDeclassement`),
  KEY `FK3FCB7A5BB4D5949` (`id_listeDeclassement`),
  KEY `FK3FCB7A5AFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FK3FCB7A5AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FK3FCB7A524DBD02B` FOREIGN KEY (`id_contenantListeDeclassement`) REFERENCES `ContenantListeDeclassement` (`id`),
  CONSTRAINT `FK3FCB7A5BB4D5949` FOREIGN KEY (`id_listeDeclassement`) REFERENCES `ListeDeclassement` (`id`),
  CONSTRAINT `FK3FCB7A5DC04CD2C` FOREIGN KEY (`id_utilisateurValidation`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `FichesDocumentsTache` (
  `id_ficheDocument` bigint(20) NOT NULL,
  `id_tache` bigint(20) NOT NULL,
  KEY `FK2D025811113B0443` (`id_ficheDocument`),
  KEY `FK2D025811AFB93BFB` (`id_tache`),
  CONSTRAINT `FK2D025811AFB93BFB` FOREIGN KEY (`id_tache`) REFERENCES `Tache` (`id`),
  CONSTRAINT `FK2D025811113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `FichesDossiersTache` (
  `id_ficheDossier` bigint(20) NOT NULL,
  `id_tache` bigint(20) NOT NULL,
  KEY `FKF99BD55AFB93BFB` (`id_tache`),
  KEY `FKF99BD55AFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FKF99BD55AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FKF99BD55AFB93BFB` FOREIGN KEY (`id_tache`) REFERENCES `Tache` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `FichierElectronique` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `encryptionKey` tinyblob,
  `numeroVersionMajeure` int(11) DEFAULT NULL,
  `numeroVersionMineure` int(11) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `dateEmprunt` datetime DEFAULT NULL,
  `id_emprunteur` bigint(20) DEFAULT NULL,
  `modifieApresEmprunt` bit(1) DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `typeMime` varchar(255) DEFAULT NULL,
  `taille` bigint(20) DEFAULT NULL,
  `md5Hex` varchar(255) DEFAULT NULL,
  `md5HexInitial` varchar(255) DEFAULT NULL,
  `invalide` bit(1) DEFAULT NULL,
  `resourceId` varchar(255) DEFAULT NULL,
  `generateNewResourceId` bit(1) DEFAULT NULL,
  `updatedResourceId` varchar(255) DEFAULT NULL,
  `apercuPossible` bit(1) DEFAULT NULL,
  `apercuGenere` bit(1) DEFAULT NULL,
  `apercuPages` int(11) DEFAULT NULL,
  `impossibleEcrireIDIntelligid` bit(1) DEFAULT NULL,
  `dateDerniereModification` datetime DEFAULT NULL,
  `datePublication` datetime DEFAULT NULL,
  `id_utilisateurPublication` bigint(20) DEFAULT NULL,
  `pdfA` bit(1) DEFAULT NULL,
  `id_soumetteur` bigint(20) DEFAULT NULL,
  `id_support` bigint(20) DEFAULT NULL,
  `voute` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKDE3D3708B04D8BA9` (`id_soumetteur`),
  KEY `FKDE3D37085E525381` (`id_utilisateurPublication`),
  KEY `FKDE3D3708F090470C` (`id_support`),
  KEY `FKDE3D370847D2C2C9` (`id_emprunteur`),
  CONSTRAINT `FKDE3D370847D2C2C9` FOREIGN KEY (`id_emprunteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKDE3D37085E525381` FOREIGN KEY (`id_utilisateurPublication`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKDE3D3708B04D8BA9` FOREIGN KEY (`id_soumetteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKDE3D3708F090470C` FOREIGN KEY (`id_support`) REFERENCES `SupportDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `FondsDocumentaire` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `titrePropre` varchar(255) DEFAULT NULL,
  `precisionsCategorieDocuments` varchar(4000) DEFAULT NULL,
  `dateCreation` datetime DEFAULT NULL,
  `etendueUniteArchivistique` varchar(4000) DEFAULT NULL,
  `histoireAdministrative` varchar(4000) DEFAULT NULL,
  `historiqueConservation` varchar(4000) DEFAULT NULL,
  `porteeContenu` varchar(255) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `FormatDocument` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKF5FE1252A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKF5FE1252A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Groupe` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `GroupeUniteAdministrative` (
  `id_groupe` bigint(20) NOT NULL,
  `id_uniteAdministrative` bigint(20) NOT NULL,
  PRIMARY KEY (`id_groupe`,`id_uniteAdministrative`),
  KEY `FKFD322329A113C8F9` (`id_groupe`),
  KEY `FKFD322329231F6571` (`id_uniteAdministrative`),
  CONSTRAINT `FKFD322329231F6571` FOREIGN KEY (`id_uniteAdministrative`) REFERENCES `UniteAdministrative` (`id`),
  CONSTRAINT `FKFD322329A113C8F9` FOREIGN KEY (`id_groupe`) REFERENCES `Groupe` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `GroupeUtilisateur` (
  `id_groupe` bigint(20) NOT NULL,
  `id_utilisateur` bigint(20) NOT NULL,
  PRIMARY KEY (`id_groupe`,`id_utilisateur`),
  KEY `FKA57A89BD67E8AF73` (`id_utilisateur`),
  KEY `FKA57A89BDA113C8F9` (`id_groupe`),
  CONSTRAINT `FKA57A89BDA113C8F9` FOREIGN KEY (`id_groupe`) REFERENCES `Groupe` (`id`),
  CONSTRAINT `FKA57A89BD67E8AF73` FOREIGN KEY (`id_utilisateur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `I18NLabel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `labelKey` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `I18NLabel_Values` (
  `idLabel` bigint(20) NOT NULL,
  `value` varchar(1024) DEFAULT NULL,
  `locale` varchar(128) NOT NULL,
  PRIMARY KEY (`idLabel`,`locale`),
  KEY `FK5D43122BA6E2C3E6` (`idLabel`),
  CONSTRAINT `FK5D43122BA6E2C3E6` FOREIGN KEY (`idLabel`) REFERENCES `I18NLabel` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `IdsDossiersParentsDocument` (
  `id_ficheDocument` bigint(20) NOT NULL,
  `id_ficheDossierParent` bigint(20) DEFAULT NULL,
  KEY `FKAD42E364113B0443` (`id_ficheDocument`),
  CONSTRAINT `FKAD42E364113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `idsdossiersparentsdossier` (
  `id_ficheDossier` bigint(20) NOT NULL,
  `id_ficheDossierParent` bigint(20) DEFAULT NULL,
  `dossierActif` bit(1) DEFAULT NULL,
  KEY `FK1F3D7F62AFBAA5E9` (`id_ficheDossier`),
  KEY `IDPD_dossierActif` (`dossierActif`),
  CONSTRAINT `FK1F3D7F62AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `idsprocessusparentsdossier` (
  `id_ficheDossier` bigint(20) NOT NULL,
  `id_processusActivite` bigint(20) DEFAULT NULL,
  `dossierActif` bit(1) DEFAULT NULL,
  KEY `FK64E91A77AFBAA5E9` (`id_ficheDossier`),
  KEY `IPAPD_dossierActif` (`dossierActif`),
  CONSTRAINT `FK64E91A77AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `idsunitesparentsdossier` (
  `id_ficheDossier` bigint(20) NOT NULL,
  `id_uniteAdministrative` bigint(20) DEFAULT NULL,
  `dossierActif` bit(1) DEFAULT NULL,
  KEY `FKF014760CAFBAA5E9` (`id_ficheDossier`),
  KEY `IUAPD_dossierActif` (`dossierActif`),
  CONSTRAINT `FKF014760CAFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `JetonFicheDocument` (
  `id_ficheDocument` bigint(20) NOT NULL,
  `jeton` varchar(255) DEFAULT NULL,
  KEY `FKC6E6E200113B0443` (`id_ficheDocument`),
  CONSTRAINT `FKC6E6E200113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `JetonFicheDossier` (
  `id_ficheDossier` bigint(20) NOT NULL,
  `jeton` varchar(255) DEFAULT NULL,
  KEY `FK20113D46AFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FK20113D46AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `JetonMetadonneeProfilMO` (
  `id_metadonnee` bigint(20) NOT NULL,
  `jeton` varchar(255) DEFAULT NULL,
  KEY `FKD40E70E63E43ACB` (`id_metadonnee`),
  CONSTRAINT `FKD40E70E63E43ACB` FOREIGN KEY (`id_metadonnee`) REFERENCES `MetadonneeProfilMO` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `JetonProcessusActivite` (
  `id_processusActivite` bigint(20) NOT NULL,
  `jeton` varchar(255) DEFAULT NULL,
  KEY `FKBD87C430F7DDF23` (`id_processusActivite`),
  CONSTRAINT `FKBD87C430F7DDF23` FOREIGN KEY (`id_processusActivite`) REFERENCES `ProcessusActivite` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `JetonUniteAdministrative` (
  `id_uniteAdministrative` bigint(20) NOT NULL,
  `jeton` varchar(255) DEFAULT NULL,
  KEY `FK9F3051B7231F6571` (`id_uniteAdministrative`),
  CONSTRAINT `FK9F3051B7231F6571` FOREIGN KEY (`id_uniteAdministrative`) REFERENCES `UniteAdministrative` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `JetonUtilisateur` (
  `id_utilisateur` bigint(20) NOT NULL,
  `jeton` varchar(255) DEFAULT NULL,
  KEY `FKF9DF264B67E8AF73` (`id_utilisateur`),
  CONSTRAINT `FKF9DF264B67E8AF73` FOREIGN KEY (`id_utilisateur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Langue` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK873AC91EA44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK873AC91EA44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `LangueDocument` (
  `id_ficheDocument` bigint(20) NOT NULL,
  `id_langue` bigint(20) NOT NULL,
  KEY `FKFF9E0059113B0443` (`id_ficheDocument`),
  KEY `FKFF9E0059CECFB3E1` (`id_langue`),
  CONSTRAINT `FKFF9E0059CECFB3E1` FOREIGN KEY (`id_langue`) REFERENCES `Langue` (`id`),
  CONSTRAINT `FKFF9E0059113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `LimiteAcces` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKCAF1EAE5A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKCAF1EAE5A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ListeDeclassement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `titre` varchar(4000) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `requeteRecherche` varchar(4000) DEFAULT NULL,
  `dateCreation` datetime DEFAULT NULL,
  `dateDemandeApprobation` datetime DEFAULT NULL,
  `dateApprobation` datetime DEFAULT NULL,
  `dateValidation` datetime DEFAULT NULL,
  `dateTraitement` datetime DEFAULT NULL,
  `supportInformatique` bit(1) DEFAULT NULL,
  `supportAnalogique` bit(1) DEFAULT NULL,
  `id_typeListe` bigint(20) DEFAULT NULL,
  `id_posteClassement` bigint(20) DEFAULT NULL,
  `id_utilisateurCreation` bigint(20) DEFAULT NULL,
  `id_utilisateurDemApprobation` bigint(20) DEFAULT NULL,
  `id_utilisateurApprobation` bigint(20) DEFAULT NULL,
  `id_utilisateurValidation` bigint(20) DEFAULT NULL,
  `id_utilisateurTraitement` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKFF71E113DC04CD2C` (`id_utilisateurValidation`),
  KEY `FKFF71E11370D306A8` (`id_utilisateurTraitement`),
  KEY `FKFF71E113A6324EC6` (`id_utilisateurApprobation`),
  KEY `FKFF71E11343214F52` (`id_utilisateurCreation`),
  KEY `FKFF71E113FACF7EF2` (`id_posteClassement`),
  KEY `FKFF71E113D795FF21` (`id_typeListe`),
  KEY `FKFF71E1134C67F718` (`id_utilisateurDemApprobation`),
  CONSTRAINT `FKFF71E1134C67F718` FOREIGN KEY (`id_utilisateurDemApprobation`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKFF71E11343214F52` FOREIGN KEY (`id_utilisateurCreation`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKFF71E11370D306A8` FOREIGN KEY (`id_utilisateurTraitement`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKFF71E113A6324EC6` FOREIGN KEY (`id_utilisateurApprobation`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKFF71E113D795FF21` FOREIGN KEY (`id_typeListe`) REFERENCES `TypeListeDeclassement` (`id`),
  CONSTRAINT `FKFF71E113DC04CD2C` FOREIGN KEY (`id_utilisateurValidation`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKFF71E113FACF7EF2` FOREIGN KEY (`id_posteClassement`) REFERENCES `UniteAdministrative` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `MasqueSaisieLocalisation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `regularExpression` varchar(255) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKA1346436A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKA1346436A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Message` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `messageType` varchar(255) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `dateEnvoi` datetime DEFAULT NULL,
  `objet` varchar(255) DEFAULT NULL,
  `message` varchar(4000) DEFAULT NULL,
  `lu` bit(1) DEFAULT NULL,
  `id_reponduPar` bigint(20) DEFAULT NULL,
  `id_enReponseDe` bigint(20) DEFAULT NULL,
  `supprimeParDestinataire` bit(1) DEFAULT NULL,
  `dernierMessageDiscussionRecu` bit(1) DEFAULT NULL,
  `id_expediteur` bigint(20) DEFAULT NULL,
  `id_destinataire` bigint(20) DEFAULT NULL,
  `typeAction` varchar(255) DEFAULT NULL,
  `typeObjet` varchar(255) DEFAULT NULL,
  `idObjet` bigint(20) DEFAULT NULL,
  `approuve` bit(1) DEFAULT NULL,
  `explicationRefus` varchar(255) DEFAULT NULL,
  `dateApprobation` datetime DEFAULT NULL,
  `processInstance` varchar(255) DEFAULT NULL,
  `id_utilisateurApprouvant` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK9C2397E7117DBEFB` (`id_utilisateurApprouvant`),
  KEY `FK9C2397E7D9C9AD73` (`id_expediteur`),
  KEY `FK9C2397E7961A220E` (`id_enReponseDe`),
  KEY `FK9C2397E7CCFFBC84` (`id_reponduPar`),
  KEY `FK9C2397E726FF26CD` (`id_destinataire`),
  CONSTRAINT `FK9C2397E726FF26CD` FOREIGN KEY (`id_destinataire`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK9C2397E7117DBEFB` FOREIGN KEY (`id_utilisateurApprouvant`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK9C2397E7961A220E` FOREIGN KEY (`id_enReponseDe`) REFERENCES `Message` (`id`),
  CONSTRAINT `FK9C2397E7CCFFBC84` FOREIGN KEY (`id_reponduPar`) REFERENCES `Message` (`id`),
  CONSTRAINT `FK9C2397E7D9C9AD73` FOREIGN KEY (`id_expediteur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `MetadonneeFondsDocumentaire` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `valeursMultiples` bit(1) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `valeurString` varchar(4000) DEFAULT NULL,
  `valeurDate` datetime DEFAULT NULL,
  `ordreMetadonnee` int(11) DEFAULT NULL,
  `id_fondsDocumentaire` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKD77A143A8B3C7CA7` (`id_fondsDocumentaire`),
  CONSTRAINT `FKD77A143A8B3C7CA7` FOREIGN KEY (`id_fondsDocumentaire`) REFERENCES `FondsDocumentaire` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `MetadonneeProfilMO` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `duplicable` bit(1) DEFAULT NULL,
  `heritee` bit(1) DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `nomPropriete` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalisee` bit(1) DEFAULT NULL,
  `question` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `conceptLie` varchar(255) DEFAULT NULL,
  `typeSaisie` int(11) DEFAULT NULL,
  `presente` bit(1) DEFAULT NULL,
  `masqueSaisie` varchar(255) DEFAULT NULL,
  `longueur` int(11) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_role` bigint(20) DEFAULT NULL,
  `id_valeurDefaut` bigint(20) DEFAULT NULL,
  `id_profilMO_dossier` bigint(20) DEFAULT NULL,
  `ordreMetadonnee` int(11) DEFAULT NULL,
  `id_profilMO_tache` bigint(20) DEFAULT NULL,
  `id_profilMO_documentCourriel` bigint(20) DEFAULT NULL,
  `id_profilMO_documentTransact` bigint(20) DEFAULT NULL,
  `assistantLookupMasque` bit(1) DEFAULT NULL,
  `id_profilMO_emplacement` bigint(20) DEFAULT NULL,
  `id_profilMO_contenant` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKCB4450CE5B4FB53A` (`id_profilMO_documentCourriel`),
  KEY `FKCB4450CE5B876371` (`id_profilMO_tache`),
  KEY `FKCB4450CEE8C41B4B` (`id_role`),
  KEY `FKCB4450CEBD9D6D7D` (`id_profilMO_documentTransact`),
  KEY `FKCB4450CE8B186E69` (`id_profilMO_dossier`),
  KEY `FKCB4450CEA44FE167` (`id_domaineValeurs`),
  KEY `FKCB4450CE434E679A` (`id_valeurDefaut`),
  KEY `FKCB4450CE8B70D95B` (`id_profilMO_emplacement`),
  KEY `FKCB4450CEB4E8AB8A` (`id_profilMO_contenant`),
  CONSTRAINT `FKCB4450CEB4E8AB8A` FOREIGN KEY (`id_profilMO_contenant`) REFERENCES `ProfilSaisieMO` (`id`),
  CONSTRAINT `FKCB4450CE434E679A` FOREIGN KEY (`id_valeurDefaut`) REFERENCES `ValeurMetadonnee` (`id`),
  CONSTRAINT `FKCB4450CE5B4FB53A` FOREIGN KEY (`id_profilMO_documentCourriel`) REFERENCES `ProfilSaisieMO` (`id`),
  CONSTRAINT `FKCB4450CE5B876371` FOREIGN KEY (`id_profilMO_tache`) REFERENCES `ProfilSaisieMO` (`id`),
  CONSTRAINT `FKCB4450CE8B186E69` FOREIGN KEY (`id_profilMO_dossier`) REFERENCES `ProfilSaisieMO` (`id`),
  CONSTRAINT `FKCB4450CE8B70D95B` FOREIGN KEY (`id_profilMO_emplacement`) REFERENCES `ProfilSaisieMO` (`id`),
  CONSTRAINT `FKCB4450CEA44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FKCB4450CEBD9D6D7D` FOREIGN KEY (`id_profilMO_documentTransact`) REFERENCES `ProfilSaisieMO` (`id`),
  CONSTRAINT `FKCB4450CEE8C41B4B` FOREIGN KEY (`id_role`) REFERENCES `RoleIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ModeExpeditionReception` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKF0D2D40BA44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKF0D2D40BA44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ModeleRapport` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `designClassname` varchar(255) DEFAULT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `nomFichier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `MotCle` (
  `id_ficheDossier` bigint(20) DEFAULT NULL,
  `motCle` varchar(4000) DEFAULT NULL,
  KEY `FK89B71CCAAFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FK89B71CCAAFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `MotsClesDelai` (
  `id_delai` bigint(20) DEFAULT NULL,
  `motCle` varchar(4000) DEFAULT NULL,
  KEY `FKAF79FEE967EA7BD9` (`id_delai`),
  CONSTRAINT `FKAF79FEE967EA7BD9` FOREIGN KEY (`id_delai`) REFERENCES `Delai` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `MotsClesProcessusActivite` (
  `id_processusActivite` bigint(20) DEFAULT NULL,
  `motCle` varchar(4000) DEFAULT NULL,
  KEY `FKE07267BEF7DDF23` (`id_processusActivite`),
  CONSTRAINT `FKE07267BEF7DDF23` FOREIGN KEY (`id_processusActivite`) REFERENCES `ProcessusActivite` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `MotsClesThesaurusDocument` (
  `id_ficheDocument` bigint(20) NOT NULL,
  `id_skosconcept` bigint(20) NOT NULL,
  KEY `FK38351CDD113B0443` (`id_ficheDocument`),
  KEY `FK38351CDDBBBB8DAB` (`id_skosconcept`),
  CONSTRAINT `FK38351CDDBBBB8DAB` FOREIGN KEY (`id_skosconcept`) REFERENCES `SkosConcept` (`id`),
  CONSTRAINT `FK38351CDD113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `MotsClesThesaurusDossier` (
  `id_ficheDossier` bigint(20) NOT NULL,
  `id_skosconcept` bigint(20) NOT NULL,
  KEY `FK86D1B2C9BBBB8DAB` (`id_skosconcept`),
  KEY `FK86D1B2C9AFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FK86D1B2C9AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FK86D1B2C9BBBB8DAB` FOREIGN KEY (`id_skosconcept`) REFERENCES `SkosConcept` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Organisation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `numero` varchar(255) DEFAULT NULL,
  `nom` varchar(4000) DEFAULT NULL,
  `adresse` varchar(4000) DEFAULT NULL,
  `municipalite` varchar(255) DEFAULT NULL,
  `codePostal` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `telecopieur` varchar(255) DEFAULT NULL,
  `courriel` varchar(255) DEFAULT NULL,
  `remarques` varchar(4000) DEFAULT NULL,
  `id_categorieCorrespondance` bigint(20) DEFAULT NULL,
  `id_provinceTerritoire` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK441E54FAD9DDC741` (`id_categorieCorrespondance`),
  KEY `FK441E54FA69C2F6B7` (`id_provinceTerritoire`),
  CONSTRAINT `FK441E54FA69C2F6B7` FOREIGN KEY (`id_provinceTerritoire`) REFERENCES `ProvinceTerritoire` (`id`),
  CONSTRAINT `FK441E54FAD9DDC741` FOREIGN KEY (`id_categorieCorrespondance`) REFERENCES `CategorieOrganisation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `PermissionIFGD` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `approuvee` bit(1) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `dateDebut` date DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `id_uniteAdministrative` bigint(20) DEFAULT NULL,
  `id_ficheDossier` bigint(20) DEFAULT NULL,
  `id_ficheDocument` bigint(20) DEFAULT NULL,
  `id_metadonnee` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK6E8951293E43ACB` (`id_metadonnee`),
  KEY `FK6E895129113B0443` (`id_ficheDocument`),
  KEY `FK6E895129AFBAA5E9` (`id_ficheDossier`),
  KEY `FK6E895129231F6571` (`id_uniteAdministrative`),
  CONSTRAINT `FK6E895129231F6571` FOREIGN KEY (`id_uniteAdministrative`) REFERENCES `UniteAdministrative` (`id`),
  CONSTRAINT `FK6E895129113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FK6E8951293E43ACB` FOREIGN KEY (`id_metadonnee`) REFERENCES `MetadonneeProfilMO` (`id`),
  CONSTRAINT `FK6E895129AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `PlanClassification` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `fondsDocumentaire` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK64DE0B8F412A76CB` (`fondsDocumentaire`),
  CONSTRAINT `FK64DE0B8F412A76CB` FOREIGN KEY (`fondsDocumentaire`) REFERENCES `FondsDocumentaire` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `PorteeGeographique` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `texte` varchar(255) DEFAULT NULL,
  `id_ficheDocument` bigint(20) DEFAULT NULL,
  `id_ficheDossier` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK4E3D4776113B0443` (`id_ficheDocument`),
  KEY `FK4E3D4776AFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FK4E3D4776AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FK4E3D4776113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `PorteeTemporelle` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `dateDebut` datetime DEFAULT NULL,
  `dateFin` datetime DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `texte` varchar(255) DEFAULT NULL,
  `id_ficheDocument` bigint(20) DEFAULT NULL,
  `id_ficheDossier` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKADBEC978113B0443` (`id_ficheDocument`),
  KEY `FKADBEC978AFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FKADBEC978AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FKADBEC978113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ProcessusActivite` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `descriptionAbregee` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `valeurTri` varchar(255) DEFAULT NULL,
  `id_planClassification` bigint(20) DEFAULT NULL,
  `id_processusActiviteParent` bigint(20) DEFAULT NULL,
  `historique` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK49A247485DE67CCD` (`id_processusActiviteParent`),
  KEY `FK49A2474887D82CAB` (`id_planClassification`),
  KEY `valeurTriPA` (`valeurTri`),
  CONSTRAINT `FK49A247485DE67CCD` FOREIGN KEY (`id_processusActiviteParent`) REFERENCES `ProcessusActivite` (`id`),
  CONSTRAINT `FK49A2474887D82CAB` FOREIGN KEY (`id_planClassification`) REFERENCES `PlanClassification` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ProfilSaisieMO` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `nomOrganisation` varchar(255) DEFAULT NULL,
  `modeNavigation` varchar(255) DEFAULT NULL,
  `modeNavigationAccueil` varchar(255) DEFAULT NULL,
  `urlOrganisation` varchar(255) DEFAULT NULL,
  `duplicationPersonnalisable` bit(1) DEFAULT NULL,
  `versionCourante` varchar(255) DEFAULT NULL,
  `dateVersionCourante` date DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `id_typeDocument` bigint(20) DEFAULT NULL,
  `id_typeDossier` bigint(20) DEFAULT NULL,
  `id_typeTache` bigint(20) DEFAULT NULL,
  `id_typeEmplacement` bigint(20) DEFAULT NULL,
  `id_typeContenant` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKA6BCB63265FAD80D` (`id_typeTache`),
  KEY `FKA6BCB632677279BD` (`id_typeDossier`),
  KEY `FKA6BCB632507DAAEF` (`id_typeDocument`),
  KEY `FKA6BCB632EC6E9721` (`id_typeEmplacement`),
  KEY `FKA6BCB6321E348FBF` (`id_typeContenant`),
  CONSTRAINT `FKA6BCB6321E348FBF` FOREIGN KEY (`id_typeContenant`) REFERENCES `TypeContenant` (`id`),
  CONSTRAINT `FKA6BCB632507DAAEF` FOREIGN KEY (`id_typeDocument`) REFERENCES `TypeDocument` (`id`),
  CONSTRAINT `FKA6BCB63265FAD80D` FOREIGN KEY (`id_typeTache`) REFERENCES `TypeTache` (`id`),
  CONSTRAINT `FKA6BCB632677279BD` FOREIGN KEY (`id_typeDossier`) REFERENCES `TypeDossier` (`id`),
  CONSTRAINT `FKA6BCB632EC6E9721` FOREIGN KEY (`id_typeEmplacement`) REFERENCES `TypeEmplacement` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ProprieteModeleRapport` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `propriete` varchar(255) DEFAULT NULL,
  `valeur` varchar(255) DEFAULT NULL,
  `id_modeleRapport` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK44D2ED2AA07A7934` (`id_modeleRapport`),
  CONSTRAINT `FK44D2ED2AA07A7934` FOREIGN KEY (`id_modeleRapport`) REFERENCES `ModeleRapport` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ProprietesFichierElectronique` (
  `id_fichierElectronique` bigint(20) NOT NULL,
  `valeurPropriete` varchar(4000) DEFAULT NULL,
  `nomPropriete` varchar(255) NOT NULL,
  PRIMARY KEY (`id_fichierElectronique`,`nomPropriete`),
  KEY `FK27E09C1142AD7103` (`id_fichierElectronique`),
  KEY `FK27E09C117CA86DE3` (`id_fichierElectronique`),
  CONSTRAINT `FK27E09C117CA86DE3` FOREIGN KEY (`id_fichierElectronique`) REFERENCES `FichierElectronique` (`id`),
  CONSTRAINT `FK27E09C1142AD7103` FOREIGN KEY (`id_fichierElectronique`) REFERENCES `FichierElectronique` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ProvinceTerritoire` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK9C788059A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK9C788059A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `PublicCibleAuditFicheDocument` (
  `id_ficheDocument` bigint(20) NOT NULL,
  `id_publicCibleAuditoire` bigint(20) NOT NULL,
  KEY `FKA3D6E869113B0443` (`id_ficheDocument`),
  KEY `FKA3D6E8691BFB3F1D` (`id_publicCibleAuditoire`),
  CONSTRAINT `FKA3D6E8691BFB3F1D` FOREIGN KEY (`id_publicCibleAuditoire`) REFERENCES `PublicCibleAuditoire` (`id`),
  CONSTRAINT `FKA3D6E869113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `PublicCibleAuditFicheDossier` (
  `id_ficheDossier` bigint(20) NOT NULL,
  `id_publicCibleAuditoire` bigint(20) NOT NULL,
  KEY `FK694245BD1BFB3F1D` (`id_publicCibleAuditoire`),
  KEY `FK694245BDAFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FK694245BDAFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FK694245BD1BFB3F1D` FOREIGN KEY (`id_publicCibleAuditoire`) REFERENCES `PublicCibleAuditoire` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `PublicCibleAuditoire` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK84DE271CA44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK84DE271CA44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Rappel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `dateRappel` datetime DEFAULT NULL,
  `nomPropriete` varchar(255) DEFAULT NULL,
  `ecartJours` int(11) DEFAULT NULL,
  `envoye` bit(1) DEFAULT NULL,
  `id_tache` bigint(20) DEFAULT NULL,
  `id_correspondance` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK9178E5767DFCBE07` (`id_correspondance`),
  KEY `FK9178E576AFB93BFB` (`id_tache`),
  CONSTRAINT `FK9178E576AFB93BFB` FOREIGN KEY (`id_tache`) REFERENCES `Tache` (`id`),
  CONSTRAINT `FK9178E5767DFCBE07` FOREIGN KEY (`id_correspondance`) REFERENCES `Correspondance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ReactivationFicheDossier` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `dateDemande` datetime DEFAULT NULL,
  `dateApprobation` datetime DEFAULT NULL,
  `dateReactivation` datetime DEFAULT NULL,
  `dateTransfertPrevue` datetime DEFAULT NULL,
  `dateTransfertPrecedente` datetime DEFAULT NULL,
  `id_ficheDossier` bigint(20) DEFAULT NULL,
  `id_utilisateurReactivation` bigint(20) DEFAULT NULL,
  `id_utilisateurDemande` bigint(20) DEFAULT NULL,
  `id_utilisateurApprobation` bigint(20) DEFAULT NULL,
  `id_utilTransfertPrecedent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK7BCBF55797B9594F` (`id_utilisateurDemande`),
  KEY `FK7BCBF557A6324EC6` (`id_utilisateurApprobation`),
  KEY `FK7BCBF557AFBAA5E9` (`id_ficheDossier`),
  KEY `FK7BCBF5574E541E3D` (`id_utilTransfertPrecedent`),
  KEY `FK7BCBF55782C3C47C` (`id_utilisateurReactivation`),
  CONSTRAINT `FK7BCBF55782C3C47C` FOREIGN KEY (`id_utilisateurReactivation`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK7BCBF5574E541E3D` FOREIGN KEY (`id_utilTransfertPrecedent`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK7BCBF55797B9594F` FOREIGN KEY (`id_utilisateurDemande`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK7BCBF557A6324EC6` FOREIGN KEY (`id_utilisateurApprobation`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK7BCBF557AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RechercheSauvegardee` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `dateSauvegarde` datetime DEFAULT NULL,
  `requete` varchar(4000) DEFAULT NULL,
  `tousUtilisateurs` bit(1) DEFAULT NULL,
  `invalide` bit(1) DEFAULT NULL,
  `id_utilisateur` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK780395A767E8AF73` (`id_utilisateur`),
  CONSTRAINT `FK780395A767E8AF73` FOREIGN KEY (`id_utilisateur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RegleConservation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `id_delai` bigint(20) NOT NULL,
  `id_processusActivite` bigint(20) DEFAULT NULL,
  `id_subdivisionUniforme` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKDDBA72EA67EA7BD9` (`id_delai`),
  KEY `FKDDBA72EA94F8E60F` (`id_subdivisionUniforme`),
  KEY `FKDDBA72EAF7DDF23` (`id_processusActivite`),
  CONSTRAINT `FKDDBA72EAF7DDF23` FOREIGN KEY (`id_processusActivite`) REFERENCES `ProcessusActivite` (`id`),
  CONSTRAINT `FKDDBA72EA67EA7BD9` FOREIGN KEY (`id_delai`) REFERENCES `Delai` (`id`),
  CONSTRAINT `FKDDBA72EA94F8E60F` FOREIGN KEY (`id_subdivisionUniforme`) REFERENCES `SubdivisionUniforme` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationAPourPartieFicheDoc` (
  `id_ficheDocumentSource` bigint(20) NOT NULL,
  `id_ficheDocumentCible` bigint(20) NOT NULL,
  KEY `FK2F65F2EB846AEA10` (`id_ficheDocumentCible`),
  KEY `FK2F65F2EB6827E55E` (`id_ficheDocumentSource`),
  CONSTRAINT `FK2F65F2EB6827E55E` FOREIGN KEY (`id_ficheDocumentSource`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FK2F65F2EB846AEA10` FOREIGN KEY (`id_ficheDocumentCible`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationAPourVersionFicheDoc` (
  `id_ficheDocumentSource` bigint(20) NOT NULL,
  `id_ficheDocumentCible` bigint(20) NOT NULL,
  KEY `FKF1ED992846AEA10` (`id_ficheDocumentCible`),
  KEY `FKF1ED9926827E55E` (`id_ficheDocumentSource`),
  CONSTRAINT `FKF1ED9926827E55E` FOREIGN KEY (`id_ficheDocumentSource`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FKF1ED992846AEA10` FOREIGN KEY (`id_ficheDocumentCible`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationEstReferParFicheDoc` (
  `id_ficheDocumentSource` bigint(20) NOT NULL,
  `id_ficheDocumentCible` bigint(20) NOT NULL,
  KEY `FK8A5D6406846AEA10` (`id_ficheDocumentCible`),
  KEY `FK8A5D64066827E55E` (`id_ficheDocumentSource`),
  CONSTRAINT `FK8A5D64066827E55E` FOREIGN KEY (`id_ficheDocumentSource`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FK8A5D6406846AEA10` FOREIGN KEY (`id_ficheDocumentCible`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationEstRemplaceParFicheDoc` (
  `id_ficheDocumentSource` bigint(20) NOT NULL,
  `id_ficheDocumentCible` bigint(20) NOT NULL,
  KEY `FK85775A5846AEA10` (`id_ficheDocumentCible`),
  KEY `FK85775A56827E55E` (`id_ficheDocumentSource`),
  CONSTRAINT `FK85775A56827E55E` FOREIGN KEY (`id_ficheDocumentSource`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FK85775A5846AEA10` FOREIGN KEY (`id_ficheDocumentCible`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationEstRequisParFicheDoc` (
  `id_ficheDocumentSource` bigint(20) NOT NULL,
  `id_ficheDocumentCible` bigint(20) NOT NULL,
  KEY `FKC7470851846AEA10` (`id_ficheDocumentCible`),
  KEY `FKC74708516827E55E` (`id_ficheDocumentSource`),
  CONSTRAINT `FKC74708516827E55E` FOREIGN KEY (`id_ficheDocumentSource`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FKC7470851846AEA10` FOREIGN KEY (`id_ficheDocumentCible`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationEstVersionDeFicheDoc` (
  `id_ficheDocumentSource` bigint(20) NOT NULL,
  `id_ficheDocumentCible` bigint(20) NOT NULL,
  KEY `FKC04940EA846AEA10` (`id_ficheDocumentCible`),
  KEY `FKC04940EA6827E55E` (`id_ficheDocumentSource`),
  CONSTRAINT `FKC04940EA6827E55E` FOREIGN KEY (`id_ficheDocumentSource`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FKC04940EA846AEA10` FOREIGN KEY (`id_ficheDocumentCible`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationRefereAFicheDocument` (
  `id_ficheDocumentSource` bigint(20) NOT NULL,
  `id_ficheDocumentCible` bigint(20) NOT NULL,
  KEY `FK9DE6BDD8846AEA10` (`id_ficheDocumentCible`),
  KEY `FK9DE6BDD86827E55E` (`id_ficheDocumentSource`),
  CONSTRAINT `FK9DE6BDD86827E55E` FOREIGN KEY (`id_ficheDocumentSource`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FK9DE6BDD8846AEA10` FOREIGN KEY (`id_ficheDocumentCible`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationRemplaceFicheDocument` (
  `id_ficheDocumentSource` bigint(20) NOT NULL,
  `id_ficheDocumentCible` bigint(20) NOT NULL,
  KEY `FK9CC79CCF846AEA10` (`id_ficheDocumentCible`),
  KEY `FK9CC79CCF6827E55E` (`id_ficheDocumentSource`),
  CONSTRAINT `FK9CC79CCF6827E55E` FOREIGN KEY (`id_ficheDocumentSource`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FK9CC79CCF846AEA10` FOREIGN KEY (`id_ficheDocumentCible`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationRequiertFicheDocument` (
  `id_ficheDocumentSource` bigint(20) NOT NULL,
  `id_ficheDocumentCible` bigint(20) NOT NULL,
  KEY `FKBE6E7607846AEA10` (`id_ficheDocumentCible`),
  KEY `FKBE6E76076827E55E` (`id_ficheDocumentSource`),
  CONSTRAINT `FKBE6E76076827E55E` FOREIGN KEY (`id_ficheDocumentSource`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FKBE6E7607846AEA10` FOREIGN KEY (`id_ficheDocumentCible`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationSeConformeAFicheDoc` (
  `id_ficheDocumentSource` bigint(20) NOT NULL,
  `id_ficheDocumentCible` bigint(20) NOT NULL,
  KEY `FKEE80C56F846AEA10` (`id_ficheDocumentCible`),
  KEY `FKEE80C56F6827E55E` (`id_ficheDocumentSource`),
  CONSTRAINT `FKEE80C56F6827E55E` FOREIGN KEY (`id_ficheDocumentSource`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FKEE80C56F846AEA10` FOREIGN KEY (`id_ficheDocumentCible`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RelationSeConformeAFicheDoss` (
  `id_ficheDossierSource` bigint(20) NOT NULL,
  `id_ficheDossierCible` bigint(20) NOT NULL,
  KEY `FKE197EAD48FD808BA` (`id_ficheDossierCible`),
  KEY `FKE197EAD455EC6884` (`id_ficheDossierSource`),
  CONSTRAINT `FKE197EAD455EC6884` FOREIGN KEY (`id_ficheDossierSource`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FKE197EAD48FD808BA` FOREIGN KEY (`id_ficheDossierCible`) REFERENCES `FicheDossier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `RoleIFGD` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKF3F0F850A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKF3F0F850A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ScolariteAuditoire` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKF2A354ACA44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKF2A354ACA44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `SequenceIFGD` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nomSequence` varchar(255) DEFAULT NULL,
  `valeurSequence` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `SignataireFicheDocTransaction` (
  `id_ficheDocumentTransaction` bigint(20) NOT NULL,
  `signataire` varchar(255) DEFAULT NULL,
  KEY `FKA72FB845EE94B9` (`id_ficheDocumentTransaction`),
  CONSTRAINT `FKA72FB845EE94B9` FOREIGN KEY (`id_ficheDocumentTransaction`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `SkosConcept` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rdfAbout` varchar(255) DEFAULT NULL,
  `skosNotes` varchar(4000) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `id_thesaurus` bigint(20) DEFAULT NULL,
  `id_broader` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKF093986C9A16F5AE` (`id_broader`),
  KEY `FKF093986C56F6D727` (`id_thesaurus`),
  CONSTRAINT `FKF093986C56F6D727` FOREIGN KEY (`id_thesaurus`) REFERENCES `Thesaurus` (`id`),
  CONSTRAINT `FKF093986C9A16F5AE` FOREIGN KEY (`id_broader`) REFERENCES `SkosConcept` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `SkosConcept_Labels` (
  `skosConcept_id` bigint(20) NOT NULL,
  `label_id` bigint(20) NOT NULL,
  PRIMARY KEY (`skosConcept_id`,`label_id`),
  KEY `FK82559F52D476E3B1` (`skosConcept_id`),
  KEY `FK82559F52CEE87773` (`label_id`),
  CONSTRAINT `FK82559F52CEE87773` FOREIGN KEY (`label_id`) REFERENCES `I18NLabel` (`id`),
  CONSTRAINT `FK82559F52D476E3B1` FOREIGN KEY (`skosConcept_id`) REFERENCES `SkosConcept` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `SkosConceptAltLabel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `skosConcept` bigint(20) DEFAULT NULL,
  `locale` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK57F6875711A8F2CF` (`skosConcept`),
  CONSTRAINT `FK57F6875711A8F2CF` FOREIGN KEY (`skosConcept`) REFERENCES `SkosConcept` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `SkosConceptAltLabel_Values` (
  `altLabelValue` bigint(20) NOT NULL,
  `value` varchar(4000) DEFAULT NULL,
  KEY `FK2D345F2AE1976774` (`altLabelValue`),
  CONSTRAINT `FK2D345F2AE1976774` FOREIGN KEY (`altLabelValue`) REFERENCES `SkosConceptAltLabel` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `SkosConceptRelation` (
  `sourceSkosConcept_id` bigint(20) NOT NULL,
  `relatedSkosConcept_id` bigint(20) NOT NULL,
  PRIMARY KEY (`sourceSkosConcept_id`,`relatedSkosConcept_id`),
  KEY `FKBF28CA88B7D0589C` (`relatedSkosConcept_id`),
  KEY `FKBF28CA888055808C` (`sourceSkosConcept_id`),
  CONSTRAINT `FKBF28CA888055808C` FOREIGN KEY (`sourceSkosConcept_id`) REFERENCES `SkosConcept` (`id`),
  CONSTRAINT `FKBF28CA88B7D0589C` FOREIGN KEY (`relatedSkosConcept_id`) REFERENCES `SkosConcept` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `StatutArchivistique` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `remarques` varchar(4000) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `StatutAutoriteEnregistrement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKD217991AA44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKD217991AA44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `StatutConfidentialite` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(3000) DEFAULT NULL,
  `notes` varchar(3000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK7045057FA44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK7045057FA44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `StatutCorrespondance` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK84444911A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK84444911A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `StatutDossier` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK93542F38A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK93542F38A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `StatutTache` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(3000) DEFAULT NULL,
  `notes` varchar(3000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK2F5C26C0A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK2F5C26C0A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `StatutUtilisateur` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK80AEE570A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK80AEE570A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `StatutVersion` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK3A65CD45A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK3A65CD45A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `SubdivisionUniforme` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `historique` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `SupportDocument` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `encryptionKey` tinyblob,
  `taille` varchar(255) DEFAULT NULL,
  `travailCollaboratif` bit(1) DEFAULT NULL,
  `id_typeSupport` bigint(20) DEFAULT NULL,
  `id_ficheDocument` bigint(20) DEFAULT NULL,
  `id_fichierElectronique` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_fichierElectronique` (`id_fichierElectronique`),
  KEY `FKD1A32DEA113B0443` (`id_ficheDocument`),
  KEY `FKD1A32DEA7CA86DE3` (`id_fichierElectronique`),
  KEY `FKD1A32DEAAE907BC5` (`id_typeSupport`),
  CONSTRAINT `FKD1A32DEAAE907BC5` FOREIGN KEY (`id_typeSupport`) REFERENCES `TypeSupport` (`id`),
  CONSTRAINT `FKD1A32DEA113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FKD1A32DEA7CA86DE3` FOREIGN KEY (`id_fichierElectronique`) REFERENCES `FichierElectronique` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Tache` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `dateCreationHorodatee` datetime DEFAULT NULL,
  `dateModificationHorodatee` datetime DEFAULT NULL,
  `ficheCompletee` bit(1) DEFAULT NULL,
  `id_utilisateurModificateur` bigint(20) DEFAULT NULL,
  `id_utilisateurSoumetteur` bigint(20) DEFAULT NULL,
  `numero` varchar(255) DEFAULT NULL,
  `resume` varchar(4000) DEFAULT NULL,
  `dateAttribution` datetime DEFAULT NULL,
  `dateReception` datetime DEFAULT NULL,
  `dateEcheance` datetime DEFAULT NULL,
  `dateRealisation` datetime DEFAULT NULL,
  `dateApprobation` datetime DEFAULT NULL,
  `dateFermeture` datetime DEFAULT NULL,
  `dateAnnulation` datetime DEFAULT NULL,
  `titre` varchar(4000) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `valeurTri` varchar(255) DEFAULT NULL,
  `id_statutTache` bigint(20) DEFAULT NULL,
  `id_posteClassement` bigint(20) DEFAULT NULL,
  `id_utilisateurExpediteur` bigint(20) DEFAULT NULL,
  `id_correspondance` bigint(20) DEFAULT NULL,
  `id_tacheParent` bigint(20) DEFAULT NULL,
  `id_utilisateurRetour` bigint(20) DEFAULT NULL,
  `id_utilisateurAttribution` bigint(20) DEFAULT NULL,
  `id_utilisateurRealisation` bigint(20) DEFAULT NULL,
  `id_utilisateurFermeture` bigint(20) DEFAULT NULL,
  `id_utilisateurAnnulation` bigint(20) DEFAULT NULL,
  `id_classificationTypeTache` bigint(20) DEFAULT NULL,
  `id_contactDestinataire` bigint(20) DEFAULT NULL,
  `id_contactExpediteur` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK4CD4EF331DD5B1B` (`id_statutTache`),
  KEY `FK4CD4EF37DFCBE07` (`id_correspondance`),
  KEY `FK4CD4EF3B4F4DC49` (`id_utilisateurModificateur`),
  KEY `FK4CD4EF3B1AC0E70` (`id_utilisateurAnnulation`),
  KEY `FK4CD4EF3FACF7EF2` (`id_posteClassement`),
  KEY `FK4CD4EF3982A95A4` (`id_utilisateurSoumetteur`),
  KEY `FK4CD4EF35F1735E7` (`id_classificationTypeTache`),
  KEY `FK4CD4EF3A0BB1565` (`id_tacheParent`),
  KEY `FK4CD4EF31754533E` (`id_utilisateurRetour`),
  KEY `FK4CD4EF3C1A6B76E` (`id_utilisateurExpediteur`),
  KEY `FK4CD4EF3C5D5CFEA` (`id_contactDestinataire`),
  KEY `FK4CD4EF38C1E41D4` (`id_utilisateurAttribution`),
  KEY `FK4CD4EF3E1790C90` (`id_contactExpediteur`),
  KEY `FK4CD4EF344720F4` (`id_utilisateurFermeture`),
  KEY `FK4CD4EF3CA8DC202` (`id_utilisateurRealisation`),
  KEY `valeurTri` (`valeurTri`),
  CONSTRAINT `FK4CD4EF3CA8DC202` FOREIGN KEY (`id_utilisateurRealisation`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK4CD4EF31754533E` FOREIGN KEY (`id_utilisateurRetour`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK4CD4EF331DD5B1B` FOREIGN KEY (`id_statutTache`) REFERENCES `StatutTache` (`id`),
  CONSTRAINT `FK4CD4EF344720F4` FOREIGN KEY (`id_utilisateurFermeture`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK4CD4EF35F1735E7` FOREIGN KEY (`id_classificationTypeTache`) REFERENCES `TypeTache` (`id`),
  CONSTRAINT `FK4CD4EF37DFCBE07` FOREIGN KEY (`id_correspondance`) REFERENCES `Correspondance` (`id`),
  CONSTRAINT `FK4CD4EF38C1E41D4` FOREIGN KEY (`id_utilisateurAttribution`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK4CD4EF3982A95A4` FOREIGN KEY (`id_utilisateurSoumetteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK4CD4EF3A0BB1565` FOREIGN KEY (`id_tacheParent`) REFERENCES `Tache` (`id`),
  CONSTRAINT `FK4CD4EF3B1AC0E70` FOREIGN KEY (`id_utilisateurAnnulation`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK4CD4EF3B4F4DC49` FOREIGN KEY (`id_utilisateurModificateur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK4CD4EF3C1A6B76E` FOREIGN KEY (`id_utilisateurExpediteur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK4CD4EF3C5D5CFEA` FOREIGN KEY (`id_contactDestinataire`) REFERENCES `Contact` (`id`),
  CONSTRAINT `FK4CD4EF3E1790C90` FOREIGN KEY (`id_contactExpediteur`) REFERENCES `Contact` (`id`),
  CONSTRAINT `FK4CD4EF3FACF7EF2` FOREIGN KEY (`id_posteClassement`) REFERENCES `UniteAdministrative` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `Thesaurus` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rdfAbout` varchar(255) DEFAULT NULL,
  `dcTitle` varchar(255) DEFAULT NULL,
  `dcDescription` varchar(255) DEFAULT NULL,
  `dcCreator` varchar(255) DEFAULT NULL,
  `dcDate` datetime DEFAULT NULL,
  `dcLanguage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TiercePartieFicheDocTransact` (
  `id_ficheDocumentTransaction` bigint(20) NOT NULL,
  `tiercePartie` varchar(255) DEFAULT NULL,
  KEY `FK625A1E7845EE94B9` (`id_ficheDocumentTransaction`),
  CONSTRAINT `FK625A1E7845EE94B9` FOREIGN KEY (`id_ficheDocumentTransaction`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeAccuseReception` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK7D4BD54F6FE75F50` (`id_elementParent`),
  KEY `FK7D4BD54FA44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK7D4BD54FA44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FK7D4BD54F6FE75F50` FOREIGN KEY (`id_elementParent`) REFERENCES `TypeAccuseReception` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeContact` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKC7781F66BAEA9867` (`id_elementParent`),
  KEY `FKC7781F66A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKC7781F66A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FKC7781F66BAEA9867` FOREIGN KEY (`id_elementParent`) REFERENCES `TypeContact` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeContenant` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKCA2B0272F3FCD7B3` (`id_elementParent`),
  KEY `FKCA2B0272A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKCA2B0272A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FKCA2B0272F3FCD7B3` FOREIGN KEY (`id_elementParent`) REFERENCES `TypeContenant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeCorrespondance` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK25CBC818ABBDEF3B` (`id_elementParent`),
  KEY `FK25CBC818A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK25CBC818A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FK25CBC818ABBDEF3B` FOREIGN KEY (`id_elementParent`) REFERENCES `TypeCorrespondance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeDocument` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK7CBB3E95B1E0B278` (`id_elementParent`),
  KEY `FK7CBB3E95A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK7CBB3E95A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FK7CBB3E95B1E0B278` FOREIGN KEY (`id_elementParent`) REFERENCES `TypeDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeDocumentDelai` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tri` varchar(255) DEFAULT NULL,
  `id_delai` bigint(20) NOT NULL,
  `id_typeDocument` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FKECB5FDE67EA7BD9` (`id_delai`),
  KEY `FKECB5FDE507DAAEF` (`id_typeDocument`),
  CONSTRAINT `FKECB5FDE507DAAEF` FOREIGN KEY (`id_typeDocument`) REFERENCES `TypeDocument` (`id`),
  CONSTRAINT `FKECB5FDE67EA7BD9` FOREIGN KEY (`id_delai`) REFERENCES `Delai` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeDossier` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKFCA47A11F016F312` (`id_elementParent`),
  KEY `FKFCA47A11A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FKFCA47A11A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FKFCA47A11F016F312` FOREIGN KEY (`id_elementParent`) REFERENCES `TypeDossier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeEmplacement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK3003AB8375CECD04` (`id_elementParent`),
  KEY `FK3003AB83A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK3003AB83A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FK3003AB8375CECD04` FOREIGN KEY (`id_elementParent`) REFERENCES `TypeEmplacement` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeListeDeclassement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK2539BCF9A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK2539BCF9A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeSupport` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `supportInformatique` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK20337B15A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK20337B15A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeSupportDossier` (
  `id_ficheDossier` bigint(20) NOT NULL,
  `id_typeSupport` bigint(20) NOT NULL,
  KEY `FK5790E9F6AFBAA5E9` (`id_ficheDossier`),
  KEY `FK5790E9F6AE907BC5` (`id_typeSupport`),
  CONSTRAINT `FK5790E9F6AE907BC5` FOREIGN KEY (`id_typeSupport`) REFERENCES `TypeSupport` (`id`),
  CONSTRAINT `FK5790E9F6AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeSupportExemplaire` (
  `id_exemplaire` bigint(20) NOT NULL,
  `id_typeSupport` bigint(20) NOT NULL,
  KEY `FK741439A7A6D98851` (`id_exemplaire`),
  KEY `FK741439A7AE907BC5` (`id_typeSupport`),
  CONSTRAINT `FK741439A7AE907BC5` FOREIGN KEY (`id_typeSupport`) REFERENCES `TypeSupport` (`id`),
  CONSTRAINT `FK741439A7A6D98851` FOREIGN KEY (`id_exemplaire`) REFERENCES `Exemplaire` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `TypeTache` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `notes` varchar(4000) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `personnalise` bit(1) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `pilotable` bit(1) DEFAULT NULL,
  `id_domaineValeurs` bigint(20) DEFAULT NULL,
  `id_elementParent` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK95C353D9B62C609A` (`id_elementParent`),
  KEY `FK95C353D9A44FE167` (`id_domaineValeurs`),
  CONSTRAINT `FK95C353D9A44FE167` FOREIGN KEY (`id_domaineValeurs`) REFERENCES `DomaineValeurs` (`id`),
  CONSTRAINT `FK95C353D9B62C609A` FOREIGN KEY (`id_elementParent`) REFERENCES `TypeTache` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `UniteAdministrative` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `nom` varchar(4000) DEFAULT NULL,
  `adresse` varchar(4000) DEFAULT NULL,
  `inactif` bit(1) DEFAULT NULL,
  `posteClassement` bit(1) DEFAULT NULL,
  `valeurTri` varchar(255) DEFAULT NULL,
  `id_uniteAdministrativeParent` bigint(20) DEFAULT NULL,
  `id_detenteurPrincipalSaisi` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK8EB66ECF8F8D41DB` (`id_uniteAdministrativeParent`),
  KEY `valeurTriUA` (`valeurTri`),
  KEY `FK8EB66ECFFEDDE33D` (`id_detenteurPrincipalSaisi`),
  CONSTRAINT `FK8EB66ECFFEDDE33D` FOREIGN KEY (`id_detenteurPrincipalSaisi`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FK8EB66ECF8F8D41DB` FOREIGN KEY (`id_uniteAdministrativeParent`) REFERENCES `UniteAdministrative` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `UniteAdministrativeAutorisee` (
  `id_uniteAdministrative` bigint(20) NOT NULL,
  `id_permission` bigint(20) NOT NULL,
  PRIMARY KEY (`id_uniteAdministrative`,`id_permission`),
  KEY `FK8F86305EF12C1545` (`id_permission`),
  KEY `FK8F86305E231F6571` (`id_uniteAdministrative`),
  CONSTRAINT `FK8F86305E231F6571` FOREIGN KEY (`id_uniteAdministrative`) REFERENCES `UniteAdministrative` (`id`),
  CONSTRAINT `FK8F86305EF12C1545` FOREIGN KEY (`id_permission`) REFERENCES `PermissionIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `UtilisateurAutorise` (
  `id_utilisateur` bigint(20) NOT NULL,
  `id_permission` bigint(20) NOT NULL,
  PRIMARY KEY (`id_utilisateur`,`id_permission`),
  KEY `FK8C95057B67E8AF73` (`id_utilisateur`),
  KEY `FK8C95057BF12C1545` (`id_permission`),
  CONSTRAINT `FK8C95057BF12C1545` FOREIGN KEY (`id_permission`) REFERENCES `PermissionIFGD` (`id`),
  CONSTRAINT `FK8C95057B67E8AF73` FOREIGN KEY (`id_utilisateur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `UtilisateurAvisCompletionTache` (
  `id_tache` bigint(20) NOT NULL,
  `id_utilisateur` bigint(20) NOT NULL,
  KEY `FK68101E1567E8AF73` (`id_utilisateur`),
  KEY `FK68101E15AFB93BFB` (`id_tache`),
  CONSTRAINT `FK68101E15AFB93BFB` FOREIGN KEY (`id_tache`) REFERENCES `Tache` (`id`),
  CONSTRAINT `FK68101E1567E8AF73` FOREIGN KEY (`id_utilisateur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `UtilisateurIFGD` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `nomUtilisateur` varchar(255) DEFAULT NULL,
  `nomUtilisateurAuthentification` varchar(255) DEFAULT NULL,
  `nomUtilisateurAuthentificationPlugin` varchar(255) DEFAULT NULL,
  `courriel` varchar(255) DEFAULT NULL,
  `motPasse` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `nomFamille` varchar(255) DEFAULT NULL,
  `dateDernierLogin` datetime DEFAULT NULL,
  `dateDesactivation` datetime DEFAULT NULL,
  `modeNavigation` varchar(255) DEFAULT NULL,
  `modeNavigationAccueil` varchar(255) DEFAULT NULL,
  `commentaire` varchar(255) DEFAULT NULL,
  `lieuTravail` varchar(255) DEFAULT NULL,
  `noTelephone` varchar(255) DEFAULT NULL,
  `fonction` varchar(255) DEFAULT NULL,
  `derniereAdresseIP` varchar(255) DEFAULT NULL,
  `domaine` varchar(255) DEFAULT NULL,
  `accesRechercheExterne` bit(1) DEFAULT NULL,
  `nombreDossiersRecents` int(11) DEFAULT NULL,
  `nombreDocumentsRecents` int(11) DEFAULT NULL,
  `tentativesConnexion` int(11) DEFAULT NULL,
  `id_statut` bigint(20) DEFAULT NULL,
  `id_role` bigint(20) DEFAULT NULL,
  `nomUtilisateurAuth` varchar(255) DEFAULT NULL,
  `nomUtilisateurAuthPlugin` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKFCAFB09DE8C41B4B` (`id_role`),
  KEY `FKFCAFB09D30DCD046` (`id_statut`),
  CONSTRAINT `FKFCAFB09D30DCD046` FOREIGN KEY (`id_statut`) REFERENCES `StatutUtilisateur` (`id`),
  CONSTRAINT `FKFCAFB09DE8C41B4B` FOREIGN KEY (`id_role`) REFERENCES `RoleIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `UtilisateurPosteClassement` (
  `id_uniteAdministrative` bigint(20) NOT NULL,
  `id_utilisateur` bigint(20) NOT NULL,
  `indexUPC` int(11) NOT NULL,
  PRIMARY KEY (`id_utilisateur`,`indexUPC`),
  KEY `FK9EFE4C8D67E8AF73` (`id_utilisateur`),
  KEY `FK9EFE4C8D231F6571` (`id_uniteAdministrative`),
  CONSTRAINT `FK9EFE4C8D231F6571` FOREIGN KEY (`id_uniteAdministrative`) REFERENCES `UniteAdministrative` (`id`),
  CONSTRAINT `FK9EFE4C8D67E8AF73` FOREIGN KEY (`id_utilisateur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `UtilisateursPorteursDossier` (
  `id_ficheDossier` bigint(20) NOT NULL,
  `id_utilisateur` bigint(20) NOT NULL,
  KEY `FKD5BCBBE967E8AF73` (`id_utilisateur`),
  KEY `FKD5BCBBE9AFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FKD5BCBBE9AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FKD5BCBBE967E8AF73` FOREIGN KEY (`id_utilisateur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurConfigurationGlobale` (
  `id_configurationGlobale` bigint(20) NOT NULL,
  `valeur` varchar(4000) DEFAULT NULL,
  KEY `FKD11CDC0DEEA96165` (`id_configurationGlobale`),
  CONSTRAINT `FKD11CDC0DEEA96165` FOREIGN KEY (`id_configurationGlobale`) REFERENCES `ConfigurationGlobale` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonnee` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `encryptionKey` tinyblob,
  `valeurString` varchar(4000) DEFAULT NULL,
  `valeurBoolean` bit(1) DEFAULT NULL,
  `valeurDate` datetime DEFAULT NULL,
  `valeurIdElement` bigint(20) DEFAULT NULL,
  `id_metadonnee` bigint(20) DEFAULT NULL,
  `id_valeurUtilisateur` bigint(20) DEFAULT NULL,
  `id_valeurUniteAdministrative` bigint(20) DEFAULT NULL,
  `id_contenant` bigint(20) DEFAULT NULL,
  `id_emplacement` bigint(20) DEFAULT NULL,
  `id_valeurFicheDossier` bigint(20) DEFAULT NULL,
  `id_valeurFicheDocument` bigint(20) DEFAULT NULL,
  `id_valeurMetadonneeCopie` bigint(20) DEFAULT NULL,
  `id_valeurMetadonneeDateJour` bigint(20) DEFAULT NULL,
  `id_valeurMetadonneeEcartDate` bigint(20) DEFAULT NULL,
  `id_valMetadonneeEcartDateJour` bigint(20) DEFAULT NULL,
  `id_valeurContenant` bigint(20) DEFAULT NULL,
  `id_ficheDocument` bigint(20) DEFAULT NULL,
  `nomPropriete` varchar(255) DEFAULT NULL,
  `id_ficheDossier` bigint(20) DEFAULT NULL,
  `id_tache` bigint(20) DEFAULT NULL,
  `id_ficheContenant` bigint(20) DEFAULT NULL,
  `id_ficheEmplacement` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKCFBEADB1CDB1C116` (`id_contenant`),
  KEY `FKCFBEADB1402A03C` (`id_valMetadonneeEcartDateJour`),
  KEY `FKCFBEADB142042CD6` (`id_valeurMetadonneeDateJour`),
  KEY `FKCFBEADB11B83D930` (`id_valeurMetadonneeEcartDate`),
  KEY `FKCFBEADB13E43ACB` (`id_metadonnee`),
  KEY `FKCFBEADB1AFB93BFB` (`id_tache`),
  KEY `FKCFBEADB1113B0443` (`id_ficheDocument`),
  KEY `FKCFBEADB1B164CEB8` (`id_emplacement`),
  KEY `FKCFBEADB14914846A` (`id_valeurFicheDossier`),
  KEY `FKCFBEADB1AFBAA5E9` (`id_ficheDossier`),
  KEY `FKCFBEADB122887CD2` (`id_valeurUtilisateur`),
  KEY `FKCFBEADB1A10763D0` (`id_valeurUniteAdministrative`),
  KEY `FKCFBEADB15CF678EA` (`id_valeurMetadonneeCopie`),
  KEY `FKCFBEADB1A31CF5E2` (`id_valeurFicheDocument`),
  KEY `FKCFBEADB12A535A35` (`id_valeurContenant`),
  KEY `FKCFBEADB1A027A81` (`id_ficheContenant`),
  KEY `FKCFBEADB11C6CD963` (`id_ficheEmplacement`),
  CONSTRAINT `FKCFBEADB11C6CD963` FOREIGN KEY (`id_ficheEmplacement`) REFERENCES `Emplacement` (`id`),
  CONSTRAINT `FKCFBEADB1113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FKCFBEADB11B83D930` FOREIGN KEY (`id_valeurMetadonneeEcartDate`) REFERENCES `ValeurMetadonneeEcartDate` (`id`),
  CONSTRAINT `FKCFBEADB122887CD2` FOREIGN KEY (`id_valeurUtilisateur`) REFERENCES `UtilisateurIFGD` (`id`),
  CONSTRAINT `FKCFBEADB12A535A35` FOREIGN KEY (`id_valeurContenant`) REFERENCES `Contenant` (`id`),
  CONSTRAINT `FKCFBEADB13E43ACB` FOREIGN KEY (`id_metadonnee`) REFERENCES `MetadonneeProfilMO` (`id`),
  CONSTRAINT `FKCFBEADB1402A03C` FOREIGN KEY (`id_valMetadonneeEcartDateJour`) REFERENCES `ValeurMetadonneeEcartDateJour` (`id`),
  CONSTRAINT `FKCFBEADB142042CD6` FOREIGN KEY (`id_valeurMetadonneeDateJour`) REFERENCES `ValeurMetadonneeDateJour` (`id`),
  CONSTRAINT `FKCFBEADB14914846A` FOREIGN KEY (`id_valeurFicheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FKCFBEADB15CF678EA` FOREIGN KEY (`id_valeurMetadonneeCopie`) REFERENCES `ValeurMetadonneeCopie` (`id`),
  CONSTRAINT `FKCFBEADB1A027A81` FOREIGN KEY (`id_ficheContenant`) REFERENCES `Contenant` (`id`),
  CONSTRAINT `FKCFBEADB1A10763D0` FOREIGN KEY (`id_valeurUniteAdministrative`) REFERENCES `UniteAdministrative` (`id`),
  CONSTRAINT `FKCFBEADB1A31CF5E2` FOREIGN KEY (`id_valeurFicheDocument`) REFERENCES `FicheDocument` (`id`),
  CONSTRAINT `FKCFBEADB1AFB93BFB` FOREIGN KEY (`id_tache`) REFERENCES `Tache` (`id`),
  CONSTRAINT `FKCFBEADB1AFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FKCFBEADB1B164CEB8` FOREIGN KEY (`id_emplacement`) REFERENCES `Emplacement` (`id`),
  CONSTRAINT `FKCFBEADB1CDB1C116` FOREIGN KEY (`id_contenant`) REFERENCES `Contenant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeContenant` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `id_valeurMetadonnee` bigint(20) DEFAULT NULL,
  `id_contenant` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKED6909FBCDB1C116` (`id_contenant`),
  KEY `FKED6909FBBC4E82F` (`id_valeurMetadonnee`),
  CONSTRAINT `FKED6909FBBC4E82F` FOREIGN KEY (`id_valeurMetadonnee`) REFERENCES `ValeurMetadonnee` (`id`),
  CONSTRAINT `FKED6909FBCDB1C116` FOREIGN KEY (`id_contenant`) REFERENCES `Contenant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeCopie` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `nomProprieteCopiee` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeDateJour` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeEcartDate` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `nomProprieteCopiee` varchar(255) DEFAULT NULL,
  `ecartJoursNegatif` bit(1) DEFAULT NULL,
  `ecartJours` int(11) DEFAULT NULL,
  `ecartMoisNegatif` bit(1) DEFAULT NULL,
  `ecartMois` int(11) DEFAULT NULL,
  `ecartAnsNegatif` bit(1) DEFAULT NULL,
  `ecartAns` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeEcartDateJour` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `ecartJoursNegatif` bit(1) DEFAULT NULL,
  `ecartJours` int(11) DEFAULT NULL,
  `ecartMoisNegatif` bit(1) DEFAULT NULL,
  `ecartMois` int(11) DEFAULT NULL,
  `ecartAnsNegatif` bit(1) DEFAULT NULL,
  `ecartAns` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeEmplacement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `id_valeurMetadonnee` bigint(20) DEFAULT NULL,
  `id_emplacement` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK7BDDF4CCBC4E82F` (`id_valeurMetadonnee`),
  KEY `FK7BDDF4CCB164CEB8` (`id_emplacement`),
  CONSTRAINT `FK7BDDF4CCB164CEB8` FOREIGN KEY (`id_emplacement`) REFERENCES `Emplacement` (`id`),
  CONSTRAINT `FK7BDDF4CCBC4E82F` FOREIGN KEY (`id_valeurMetadonnee`) REFERENCES `ValeurMetadonnee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeFicheDocument` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `id_valeurMetadonnee` bigint(20) DEFAULT NULL,
  `id_ficheDocument` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK28C98B07113B0443` (`id_ficheDocument`),
  KEY `FK28C98B07BC4E82F` (`id_valeurMetadonnee`),
  CONSTRAINT `FK28C98B07BC4E82F` FOREIGN KEY (`id_valeurMetadonnee`) REFERENCES `ValeurMetadonnee` (`id`),
  CONSTRAINT `FK28C98B07113B0443` FOREIGN KEY (`id_ficheDocument`) REFERENCES `FicheDocument` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeFicheDossier` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `id_valeurMetadonnee` bigint(20) DEFAULT NULL,
  `id_ficheDossier` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK4441D75FBC4E82F` (`id_valeurMetadonnee`),
  KEY `FK4441D75FAFBAA5E9` (`id_ficheDossier`),
  CONSTRAINT `FK4441D75FAFBAA5E9` FOREIGN KEY (`id_ficheDossier`) REFERENCES `FicheDossier` (`id`),
  CONSTRAINT `FK4441D75FBC4E82F` FOREIGN KEY (`id_valeurMetadonnee`) REFERENCES `ValeurMetadonnee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeListeDates` (
  `id_valeurMetadonnee` bigint(20) NOT NULL,
  `valeur` datetime DEFAULT NULL,
  KEY `FKE1C8AFCFBC4E82F` (`id_valeurMetadonnee`),
  CONSTRAINT `FKE1C8AFCFBC4E82F` FOREIGN KEY (`id_valeurMetadonnee`) REFERENCES `ValeurMetadonnee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeListeIdsEDVs` (
  `id_valeurMetadonnee` bigint(20) NOT NULL,
  `id_element` bigint(20) DEFAULT NULL,
  KEY `FK9FDC613EBC4E82F` (`id_valeurMetadonnee`),
  CONSTRAINT `FK9FDC613EBC4E82F` FOREIGN KEY (`id_valeurMetadonnee`) REFERENCES `ValeurMetadonnee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeListeStrings` (
  `id_valeurMetadonnee` bigint(20) NOT NULL,
  `valeur` varchar(3000) DEFAULT NULL,
  KEY `FKCC2B214CBC4E82F` (`id_valeurMetadonnee`),
  CONSTRAINT `FKCC2B214CBC4E82F` FOREIGN KEY (`id_valeurMetadonnee`) REFERENCES `ValeurMetadonnee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeUniteAdmin` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `id_valeurMetadonnee` bigint(20) DEFAULT NULL,
  `id_uniteAdministrative` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK4EBF10FFBC4E82F` (`id_valeurMetadonnee`),
  KEY `FK4EBF10FF231F6571` (`id_uniteAdministrative`),
  CONSTRAINT `FK4EBF10FF231F6571` FOREIGN KEY (`id_uniteAdministrative`) REFERENCES `UniteAdministrative` (`id`),
  CONSTRAINT `FK4EBF10FFBC4E82F` FOREIGN KEY (`id_valeurMetadonnee`) REFERENCES `ValeurMetadonnee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ValeurMetadonneeUtilisateur` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `id_valeurMetadonnee` bigint(20) DEFAULT NULL,
  `id_utilisateur` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK2C96651267E8AF73` (`id_utilisateur`),
  KEY `FK2C966512BC4E82F` (`id_valeurMetadonnee`),
  CONSTRAINT `FK2C966512BC4E82F` FOREIGN KEY (`id_valeurMetadonnee`) REFERENCES `ValeurMetadonnee` (`id`),
  CONSTRAINT `FK2C96651267E8AF73` FOREIGN KEY (`id_utilisateur`) REFERENCES `UtilisateurIFGD` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

