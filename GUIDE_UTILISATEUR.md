# Guide Utilisateur Gest-IPM (Scindé par Rôle Métier)

Bienvenue dans le guide d'utilisation officiel de la plateforme **Gest-IPM**, le système intégré de gestion de l'Institution de Prévoyance Maladie.

Ce document présente le fonctionnement complet de l'application, organisé **spécifiquement par profil et rôle métier**, afin que chaque collaborateur maîtrise rapidement son espace de travail et ses responsabilités.

---

## Sommaire

1. [Présentation Générale & Authentification](#1-présentation-générale--authentification)
2. [Matrice Globale des Rôles & Accès](#2-matrice-globale-des-rôles--accès)
3. [Guide par Rôle Métier](#3-guide-par-rôle-métier)
   - [3.1 Administrateur Système](#31-administrateur-système)
   - [3.2 Superviseur (Mode Lecteur & Décisionnel)](#32-superviseur-mode-lecteur--décisionnel)
   - [3.3 Pôle Facturation & Recouvrement](#33-pôle-facturation--recouvrement)
     - [Gestionnaire Facturation](#gestionnaire-facturation)
     - [Agent de Recouvrement](#agent-de-recouvrement)
   - [3.4 Pôle Prises en Charge & Prestations Médicales](#34-pôle-prises-en-charge--prestations-médicales)
     - [Gestionnaire Prises en Charge](#gestionnaire-prises-en-charge)
     - [Agent Saisie Prestations](#agent-saisie-prestations)
     - [Médecin Conseil](#médecin-conseil)
   - [3.5 Pôle Adhérents & Bénéficiaires](#35-pôle-adhérents--bénéficiaires)
     - [Gestionnaire Adhérents (Entreprises)](#gestionnaire-adhérents-entreprises)
     - [Gestionnaire Bénéficiaires (Participants & Familles)](#gestionnaire-bénéficiaires-participants--familles)
   - [3.6 Pôle Réseau Médical & Couvertures](#36-pôle-réseau-médical--couvertures)
     - [Gestionnaire Réseau Médical](#gestionnaire-réseau-médical)
     - [Gestionnaire des Couvertures](#gestionnaire-des-couvertures)
   - [3.7 Pôle Contrôle Interne & Audit](#37-pôle-contrôle-interne--audit)
     - [Auditeur & Contrôle Interne](#auditeur--contrôle-interne)
   - [3.8 Pôle Gestion Documentaire](#38-pôle-gestion-documentaire)
     - [Gestionnaire Documentaire](#gestionnaire-documentaire)
4. [Foire Aux Questions (FAQ) & Bonnes Pratiques](#4-foire-aux-questions-faq--bonnes-pratiques)

---

## 1. Présentation Générale & Authentification

### 1.1 Objectifs de Gest-IPM
Gest-IPM centralise toute la chaîne de prévoyance maladie :
- L'adhésion des entreprises et le suivi de leurs cotisations.
- L'immatriculation des participants (salariés) et de leurs ayants droit.
- L'émission et la validation des prises en charge (Bons de commande pharmacie, Lettres de garantie d'hospitalisation, Feuilles de maladie).
- La liquidation des prestations médicales avec application automatique des taux conventionnels (75%, 80%, 100%) et des plafonds de remboursement.
- Le suivi de la facturation des prestataires de santé et de leurs paiements.
- Le conventionnement des praticiens, cliniques et pharmacies.
- La traçabilité intégrale de toutes les opérations via un journal d'audit.

### 1.2 Connexion & Session
1. Rendez-vous sur l'adresse de l'application (ex. `http://127.0.0.1:8000/login`).
2. Saisissez votre **identifiant (Email ou Login)** et votre **mot de passe**.
3. Cliquez sur **Se connecter**.
4. En cas de session inactive, une reconnexion automatique vous sera demandée par mesure de sécurité.
5. Pour vous déconnecter, cliquez sur votre nom en haut à droite, puis sur **Déconnexion**.

### 1.3 Éléments d'interface communs
- **Menu supérieur / Barre latérale** : Permet de naviguer entre les différents modules auxquels votre rôle vous donne droit.
- **Bannière "Mode Consultation / Lecteur"** : Présente uniquement pour les profils en lecture seule (notamment le Superviseur), indiquant que la modification des données est verrouillée.
- **Recherche prédictive & Autocomplétion** : Saisissez quelques lettres ou le matricule pour trouver instantanément un adhérent, un participant ou un prestataire.
- **Badges de statut** :
  - <span style="color: #198754; font-weight: bold;">Vert (Succès / Approuvée / Soldée / Actif)</span>
  - <span style="color: #ffc107; font-weight: bold;">Jaune / Orange (En attente / Partiel / Suspendu)</span>
  - <span style="color: #dc3545; font-weight: bold;">Rouge (Rejetée / Impayée / Radié)</span>

---

## 2. Matrice Globale des Rôles & Accès

| Rôle Métier | Code Système | Périmètre Principal | Droits d'Édition | KPIs Financiers (Reporting) |
| :--- | :--- | :--- | :---: | :---: |
| **Administrateur** | `Administrateur` | Gestion globale, sécurité, rôles, paramètres | **OUI** | **OUI** |
| **Superviseur** | `superviseur` | Vue d'ensemble 360°, contrôle, exports et téléchargements | **NON** (Lecteur) | **OUI** |
| **Gestionnaire Facturation** | `role_facturation` / `gestionnaire_facturation_n1` | Factures prestataires, paiements, cotisations | **OUI** | **OUI** |
| **Agent de Recouvrement** | `agent_de_recouvrement` | Suivi et relances des cotisations entreprises | **OUI** | **NON** |
| **Gestionnaire Prises en Charge** | `role_demandes` | Émission, validation/rejet des demandes, bons PDF | **OUI** | **NON** |
| **Agent Saisie Prestations** | `role_prestations` / `agent_saisie` | Saisie prestations, calcul part IPM, exports Excel | **OUI** | **NON** |
| **Médecin Conseil** | `role_medical` | Contrôle médical, entente préalable, dossiers médicaux | **OUI** (Avis) | **NON** |
| **Gestionnaire Adhérents** | `role_entreprises` | Entreprises adhérentes, statuts, cotisations | **OUI** | **NON** |
| **Gestionnaire Bénéficiaires** | `role_salaries` | Immatriculation salariés, ayants droit, cartes d'assurés | **OUI** | **NON** |
| **Gestionnaire Réseau Médical** | `role_prestataires` | Praticiens, cliniques, pharmacies conventionnées | **OUI** | **NON** |
| **Gestionnaire des Couvertures** | `role_couverture` | Nomenclature actes, taux de couverture, plafonds | **OUI** | **NON** |
| **Auditeur & Contrôle Interne** | `role_audit` | Piste d'audit, traçabilité des flux, export journal | **NON** (Lecteur) | **NON** |
| **Gestionnaire Documentaire** | `role_documents` | Pièces jointes, justificatifs, médiathèque | **OUI** | **NON** |

---

## 3. Guide par Rôle Métier

```
                            ┌─────────────────────────────────────────────────┐
                            │                  GEST-IPM HUB                   │
                            └────────────────────────┬────────────────────────┘
                                                     │
         ┌────────────────────────┬──────────────────┴───────────────┬────────────────────────┐
         ▼                        ▼                                  ▼                        ▼
┌──────────────────┐    ┌──────────────────┐               ┌──────────────────┐    ┌──────────────────┐
│  ADMINISTRATEUR  │    │   SUPERVISEUR    │               │ POLE FACTURATION │    │   POLE MEDICAL   │
│ Sécurité & Rôles │    │  Vue 360° & Docs │               │ Factures & Règl. │    │ PEC & Prestation │
└──────────────────┘    └──────────────────┘               └──────────────────┘    └──────────────────┘
```

---

### 3.1 Administrateur Système

Le rôle **Administrateur** dispose des droits absolus sur l'ensemble du système d'information de l'IPM.

#### Missions Clés
1. **Gestion des utilisateurs et des permissions** (`Menu > Utilisateurs & Rôles`) :
   - Créer de nouveaux comptes utilisateurs.
   - Assigner un rôle métier adapté à la fonction de chaque agent.
   - Activer ou désactiver des accès en cas de départ ou changement de poste.
2. **Paramétrage des actes et des couvertures** (`Menu > Paramètres > Couvertures`) :
   - Définir les actes pris en charge (Consultations, Médicaments, Hospitalisation, Maternité, Optique, Biologie, Dentaire).
   - Configurer les taux de couverture IPM (ex. 75% Pharmacie, 80% Actes généraux, 100% Hospitalisation d'urgence).
   - Configurer les plafonds réglementaires (Hospitalisation : 20 000 FCFA, Optique : 30 000 FCFA, Maternité : 50 000 FCFA).
3. **Piste d'audit et sécurité** (`Menu > Audit`) :
   - Surveiller les connexions suspectes ou tentatives d'accès non autorisées.
   - Vérifier l'historique des modifications critiques (suppressions de factures, modifications de taux).

#### Procédure : Créer un utilisateur et lui assigner un rôle
1. Allez dans **Gestion des Utilisateurs**.
2. Cliquez sur **+ Nouvel Utilisateur**.
3. Remplissez le nom, prénom, email, identifiant (login) et mot de passe provisoire.
4. Sélectionnez le **Rôle Métier** adéquat dans la liste déroulante (ex: `Gestionnaire Facturation`, `Superviseur`, etc.).
5. Cliquez sur **Enregistrer**.

---

### 3.2 Superviseur (Mode Lecteur & Décisionnel)

Le rôle **Superviseur** est un profil stratégique de direction, de coordination et de contrôle. Il dispose d'une visibilité transversale complète sans risque d'altération accidentelle des données.

#### Caractéristiques Principales
- **Lecture seule intégrale** : Les boutons d'action de saisie, modification ou suppression sont automatiquement masqués. L'interface affiche le badge informatif :
  `Mode Consultation / Lecteur : Les actions d'édition et d'ajout sont désactivées sur ce profil.`
- **Capacité de génération et de téléchargement** : Le superviseur peut exporter et télécharger tout document produit par l'IPM.

#### Utilisation du Tableau de Bord & Reporting
En accédant au menu **Tableau de Bord & Reporting**, le Superviseur dispose des indicateurs opérationnels et financiers :
1. **Cartes KPIs du haut** :
   - **Adhérents** : Nombre total d'entreprises adhérentes.
   - **Bénéficiaires** : Total des assurés (salariés cotisants + ayants droit).
   - **Total Facturé** : Montant cumulé des factures émises par le réseau de santé.
   - **Reste à Payer** : Montant restant dû par l'IPM aux prestataires de santé.
2. **Graphique d'Évolution des Facturations** : Suivi mensuel des dépenses de santé sur les 6 derniers mois.
3. **Statut des Demandes** : Diagramme circulaire des prises en charge (Approuvées, En attente, Rejetées).
4. **Réseau de Santé** : Synthèse du nombre de praticiens et pharmacies conventionnés.
5. **Dernières Factures Émises** : Tableau récapitulatif des factures récentes avec statut de paiement.

#### Procédure : Consulter et Télécharger des Documents
- **Prises en charge (Bons / Lettres)** : Allez dans `Prises en charge`, cliquez sur une demande approuvée, puis cliquez sur **Télécharger le Bon PDF**.
- **Prestations médicales** : Allez dans `Prestations`, filtrez par période ou bénéficiaire, puis cliquez sur le bouton vert **Exporter XLSX**.
- **Audit de conformité** : Allez dans `Audit`, filtrez les logs, puis cliquez sur **Exporter Audit**.

---

### 3.3 Pôle Facturation & Recouvrement

#### Gestionnaire Facturation
Le gestionnaire de facturation assure le traitement financier des prestations fournies par les partenaires de santé conventionnés.

##### Missions :
- Enregistrer les factures déposées par les praticiens, cliniques et pharmacies.
- Rapprocher les factures des prises en charge et prestations réelles.
- Effectuer et enregistrer les paiements (acomptes ou règlements complets).
- Visualiser les indicateurs financiers sur le tableau de bord.

##### Procédure : Enregistrer une Facture Prestataire
1. Allez dans le menu **Facturation & Règlements** > **Factures**.
2. Cliquez sur **+ Nouvelle Facture**.
3. Renseignez :
   - Le **Numéro de facture** (référence du prestataire).
   - La **Date de facture**.
   - Le **Prestataire** (sélectionnez la Pharmacie ou le Praticien / Clinique).
   - Le **Montant total**.
4. Associez les prestations ou prises en charge correspondantes.
5. Cliquez sur **Enregistrer la facture**.

##### Procédure : Enregistrer un Paiement
1. Ouvrez la facture concernée en cliquant sur sa ligne.
2. Dans l'encart **Règlements & Paiements**, cliquez sur **+ Enregistrer un paiement**.
3. Renseignez le montant payé, la date de paiement, le mode de règlement (Virement bancaire, Chèque, Caisse/Espèces) et la référence de la transaction.
4. Cliquez sur **Valider le paiement**. Le statut de la facture passe automatiquement à *Partiellement payée* ou *Soldée*.

---

#### Agent de Recouvrement
L'agent de recouvrement veille à la bonne rentrée des cotisations patronales et salariales des entreprises adhérentes.

##### Missions :
- Suivre les échéances de cotisations par entreprise.
- Identifier les entreprises en retard de paiement.
- Émettre les avis de relance.
- Enregistrer les encaissements de cotisations.

##### Procédure : Suivi des cotisations et relances
1. Allez dans le menu **Cotisations**.
2. Filtrez par statut **En attente** ou **Impayée**.
3. Cliquez sur **Relancer l'entreprise** pour générer une notification automatique par email ou un courrier de relance avec le montant total dû.
4. Dès réception du virement ou chèque, cliquez sur **Enregistrer l'encaissement**.

---

### 3.4 Pôle Prises en Charge & Prestations Médicales

```
┌────────────────────────┐      Validation     ┌────────────────────────┐      Saisie Prestation    ┌────────────────────────┐
│ Demande Prise en Charge│ ──────────────────> │ Prise en Charge Validée│ ────────────────────────> │  Prestation Liquidée   │
│ (Pharmacie / Clinique) │   (Taux & Droits)   │   (Bon / Lettre PDF)   │   (Part IPM / Ticket)     │ (Reste à charge calculé│
└────────────────────────┘                     └────────────────────────┘                           └────────────────────────┘
```

#### Gestionnaire Prises en Charge
Ce rôle instruit et valide les demandes de prise en charge présentées par les assurés ou envoyées par les prestataires.

##### Types de Prises en Charge :
1. **Bon de commande** : Délivré pour la délivrance de médicaments en pharmacie conventionnée (Taux IPM standard : 75%).
2. **Lettre de garantie** : Délivrée pour une hospitalisation ou une intervention en clinique/hôpital conventionné.
3. **Feuille de maladie** : Délivrée pour une consultation chez un praticien (médecin généraliste, spécialiste).

##### Procédure : Émettre une Prise en Charge
1. Rendez-vous dans **Prises en charge** > **+ Nouvelle Prise en charge**.
2. Recherchez le participant par son **Matricule** ou son **Nom** (autocomplétion instantanée).
3. Sélectionnez le bénéficiaire réel (le salarié lui-même ou l'un de ses ayants droit déclarés).
4. Le système vérifie automatiquement :
   - La validité des droits du salarié (statut actif).
   - L'âge de l'ayant droit (si enfant, doit avoir moins de 21 ans).
   - La régularité de l'entreprise cotisante.
5. Sélectionnez le type d'acte et le partenaire conventionné (Pharmacie ou Praticien).
6. Cliquez sur **Soumettre la demande**.
7. En tant que gestionnaire habilité, cliquez sur **Approuver** (ou **Rejeter** en motivant le motif).
8. Cliquez sur **Imprimer / Télécharger le Bon** pour le remettre à l'assuré.

---

#### Agent Saisie Prestations
L'agent saisie prestations enregistre les actes médicaux effectivement réalisés et liquide le décompte de remboursement.

##### Missions :
- Enregistrer les prestations sur la base d'une prise en charge préalablement validée.
- Vérifier l'application stricte des taux conventionnels et des plafonds.
- Exporter les journaux de prestations pour la comptabilité.

##### Procédure : Saisir une Prestation
1. Allez dans **Prestations Médicales** > **+ Saisir une prestation**.
2. Sélectionnez la **Prise en charge validée** source.
   > **Fonctionnalité d'auto-remplissage :** Le praticien, la clinique ou la pharmacie conventionnée est automatiquement pré-rempli dans le champ partenaire de santé !
3. Saisissez le montant total de l'acte ou de l'ordonnance.
4. Le système calcule instantanément :
   - **Taux de couverture IPM** (ex. 75%, 80%, 100%).
   - **Plafond légal** (ex. si Hospitalisation dépasse 20 000 FCFA, la part IPM est plafonnée à 20 000 FCFA).
   - **Part prise en charge par l'IPM**.
   - **Reste à charge (ticket modérateur)** dû par le salarié.
5. Cliquez sur **Enregistrer la prestation**.
6. Pour exporter la liste au format tableur, cliquez sur **Exporter XLSX**.

---

#### Médecin Conseil
Le médecin conseil apporte l'expertise médicale nécessaire aux actes soumis à entente préalable.

##### Missions :
- Examiner les demandes d'actes lourds, d'hospitalisation prolongée ou d'examens spécialisés (IRM, Scanner, prothèses).
- Donner un avis médical : *Favorable*, *Défavorable* ou *Demande de complément d'information*.
- Consulter le **Dossier Médical** de l'assuré dans le respect strict du secret médical.

---

### 3.5 Pôle Adhérents & Bénéficiaires

#### Gestionnaire Adhérents (Entreprises)
Gère le portefeuille des entreprises partenaires dont les salariés bénéficient de la couverture IPM.

##### Procédure : Affilier une Entreprise
1. Allez dans **Adhérents** > **+ Nouvelle entreprise**.
2. Saisissez la **Raison sociale**, le numéro **NINEA**, l'adresse, le téléphone et l'email du contact RH.
3. Renseignez le taux de cotisation conventionnel.
4. Cliquez sur **Enregistrer**.
5. *Note importante : Si une entreprise est radiée ou suspendue pour défaut de cotisation, une alerte est automatiquement levée lors de toute tentative de prise en charge pour l'un de ses salariés.*

---

#### Gestionnaire Bénéficiaires (Participants & Familles)
Gère l'immatriculation des travailleurs et de leurs familles.

##### Procédure : Immatriculer un Salarié
1. Allez dans **Participants** > **+ Nouveau Participant**.
2. Renseignez l'état civil : Nom, Prénom, Date de naissance, Sexe, Téléphone, Adresse.
3. Sélectionnez l'**Entreprise employeur**.
4. Téléversez la **Photo d'identité** (qui figurera sur la carte d'assuré).
5. Cliquez sur **Enregistrer**. Un **Matricule IPM unique** est généré automatiquement.

##### Procédure : Ajouter un Ayant Droit (Famille)
1. Ouvrez la fiche du salarié.
2. Dans l'onglet **Ayants Droit**, cliquez sur **+ Ajouter un ayant droit**.
3. Indiquez le lien de parenté :
   - **Conjoint(e)** : fournir l'acte de mariage.
   - **Enfant** : fournir l'acte de naissance (couvert jusqu'à 21 ans révolus).
4. Cliquez sur **Enregistrer**.

##### Procédure : Générer et Imprimer la Carte d'Assuré
1. Sur la fiche du salarié ou de l'ayant droit, cliquez sur **Générer la Carte d'Assuré**.
2. Le système crée une carte numérique officielle intégrant :
   - Le logo IPM.
   - La photo d'identité.
   - Le nom, prénom, matricule et entreprise.
   - Le QR Code de vérification rapide pour les pharmacies et cliniques.
3. Cliquez sur **Imprimer la Carte** (format badge plastifié).

---

### 3.6 Pôle Réseau Médical & Couvertures

#### Gestionnaire Réseau Médical
Gère les conventions avec les prestataires de santé du pays.

##### Missions :
- Créer et mettre à jour les fiches des **Praticiens** (Nom, spécialité, contact, numéro de convention).
- Créer et mettre à jour les fiches des **Pharmacies** (Nom, localisation, convention).
- Désactiver un partenaire en cas de rupture de convention ou de litige.

---

#### Gestionnaire des Couvertures
Définit la politique de remboursement de l'IPM conformément aux statuts et à la réglementation.

##### Missions :
- Référencer les nouveaux actes médicaux dans la nomenclature.
- Fixer les taux de participation IPM :
  - **Médicaments (Pharmacie)** : 75%
  - **Consultations & Soins courants** : 80%
  - **Hospitalisation** : 100% avec plafond de 20 000 FCFA
  - **Optique** : Plafond de 30 000 FCFA
  - **Maternité** : Plafond de 50 000 FCFA

---

### 3.7 Pôle Contrôle Interne & Audit

#### Auditeur & Contrôle Interne
Ce rôle assure la transparence, la conformité réglementaire et la prévention des fraudes.

##### Missions :
- Consulter le journal d'audit chronologique (`Menu > Journal d'Audit`).
- Filtrer par utilisateur, date, type d'action (Connexion, Création, Modification, Validation, Suppression) ou entité ciblée.
- Exporter les journaux d'audit au format tableur pour transmission aux commissaires aux comptes ou à la direction générale.

---

### 3.8 Pôle Gestion Documentaire

#### Gestionnaire Documentaire
Assure la numérisation et l'archivage de tous les documents justificatifs.

##### Missions :
- Téléverser et classer les pièces jointes (ordonnances scannées, bulletins de salaire, pièces d'état civil, factures prestataires numérisées).
- Maintenir la médiathèque organisée avec des métadonnées claires pour faciliter la recherche par dossier d'assuré.

---

## 4. Foire Aux Questions (FAQ) & Bonnes Pratiques

### Q1 : Pourquoi un agent ne voit-il pas le « Total Facturé » et le « Reste à Payer » sur son tableau de bord ?
> **Réponse :** Pour des raisons de confidentialité financière et d'étanchéité des responsabilités, les montants financiers globaux sont réservés aux **Services de Facturation**, au **Superviseur** et à l'**Administrateur**. Les autres utilisateurs visualisent uniquement les statistiques d'adhérents, bénéficiaires et le réseau médical.

### Q2 : Un salarié actif peut-il bénéficier d'un bon si son entreprise a des cotisations en retard ?
> **Réponse :** Le système affiche un avertissement. Si l'entreprise est marquée comme **Suspendue**, la validation du bon sera bloquée jusqu'à régularisation par le service de recouvrement.

### Q3 : Pourquoi le bouton de création ou de validation n'apparaît-il pas sur mon compte ?
> **Réponse :** Si vous êtes connecté avec le profil **Superviseur**, votre compte est configuré en mode lecture seule pour garantir l'intégrité des données lors des revues de contrôle. Pour toute modification opérationnelle, contactez votre administrateur ou l'agent en charge du dossier.

### Q4 : Que se passe-t-il lorsqu'un salarié est radié ?
> **Réponse :** Le système de cascade automatique de Gest-IPM met instantanément à jour le statut de tous ses ayants droit rattachés en **Radié**, empêchant toute émission indue de prise en charge.

### Q5 : Comment réinitialiser le mot de passe d'un utilisateur ?
> **Réponse :** Seul l'Administrateur peut réinitialiser un mot de passe depuis le menu `Utilisateurs & Rôles` > `Modifier l'utilisateur`.

---

*Guide officiel Gest-IPM - Version 2.0 - Institution de Prévoyance Maladie*
