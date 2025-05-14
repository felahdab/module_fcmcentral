## 0.0.6 (May 14, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.5 –> 0.0.6
- Rajout d'un élément d'accès direct pour illustrer la possibilité.
- Retrait du test de la ressource FcmMarin.

## 0.0.5 (May 02, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.4 –> 0.0.5
- La ressource FcmMarin ne semble plus nécessaire. Retirée temporairement pour vérifier.
- Passage du livret de FCM dans la ressource Marin.  Adaptation du Livret de FCM pour travailler depuis une page de ressource Marin et non FcmMarin. Ajustement des actions de la ressource Marin pour utiliser les évènements de FcmCommun. Ajustement des actions de la page de recherche Annuaire pour la même chose.
- Implements art #463585 Creation Event Sauve

## 0.0.4 (April 18, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.3 –> 0.0.4
- Ajustement de l'affichage dans la liste des marins.
- Ajustement du formulaire de l'action de la page de recherche dans l'annuaire.
- Merge branch 'master' into tuleap-459003-deplacer-la-page-listant-les-marins-en-fcm-dans-les-modules-fcm-central-et-fcm-unite
- Modification mineure pour éviter les exceptions lorsqu'un marien n'a pas de parcours sérialisé.
- Mise en place de la liste des marins dans le menu "Marins".
- Rajout d'une resource reposant sur le modèle Marin de FcmCentral dans le panneau FcmCentral. Permet d'afficher la liste des marins connus dans l'instance et de leur appliquer des actions spécifiques à la FCM si on le souhite.
- Création des méthodes pour faciliter l'accès aux données de la FCM depuis les objets de type Marin.
- Ajout commentaire sur scope mentor.
- Retrait de la Policy destinée au Marin du module FcmCentral: la policy doit reposer sur celle du module RH.
- Définition de l'attribut complements_fcm, du scope cohorte, du scope mentorePar et du scope mentor.
- Création de la page de recherche dans l'annuaire. Reste à définir les actions à réaliser.
- Création de la page de recherche dans l'annuaire avec les formulaires associés.
- Ajout d'un groupe aux tests pour faciliter le test partiel.
- Adaptation de la page de configuration du module pour la récupération des cohortes. Définition des premiers tests du module.
- Création d'un endpoint API pour exposer les cohortes connues.

## 0.0.3 (April 16, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.2 –> 0.0.3
- Merge branch 'tuleap-419581-refonte-tables-fcmcentral-modification-filament-fcmcentral'
- Ajustement PSR-4
- Duplication des pages de gestion des cohortes dans le module FCM Central
- implements art #419581
- implements art #419581
- Suppression fichier inutile.
- Implements art #419581 : Update et ParcoursSerialise
- Merge remote-tracking branch 'origin/tuleap-419581-refonte-tables-fcmcentral-modification-filament-fcmcentral'
- Implements art #419581 : Mise a jour Events Listener pour Fcm Suivi Marin Creation des Events Listeners pour l'enregistrement des marins en FCM Notification avec Filament , logs ...
- Refonte des Tables Migration + Seeders ligne commande : php artisan julien:refresh-db Gestion  des Parcours, page Parcours View Travaux sur plusieurs modules FcmCommun, FcmCentral, FcmUnite, RH
- Ajustement des scripts npm pour le dev.
- Ajustement de la commande de dev npm pour mettre à jour automatiquement le theme lors du développement.
- Ajustement configuration tailwind et npm
- Merge remote-tracking branch 'internet/master'
- Introduction du nécessaire pour la customization CSS
- Merge branch 'tuleap-418609-creation-tables-fcm-marins' into tuleap-419581-refonte-tables-fcmcentral-modification-filament-fcmcentral
- Implements art #418609 :  Moduel FcmCentral : creation tables FcmCentral|UniteMarins , creations models et modification model RH_marin pour la liaison OneToMany
- Suppression des widgets inutiles du dashboard
- Merge remote-tracking branch 'internet/master'
- WIP api
- wip
- Wip events et API
- wip configuration du module
- Creation d'une page de configuration du module.
- Changement id du panneau pour affichage
- wip
- wip
- Correction bug en cas de retrait du filtre par la croix
- wip
- wip
- wip
- Nettoyage
- UserResource is now MarinResource
- Ajustement des elements pour tenir compte du module RH
- Ajustement des migrations.
- Correction typo routes api
- Utilisation du livret Livewire.

## 0.0.2 (September 09, 2024)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.1 –> 0.0.2
- ajout bump-version

## 0.0.1 (September 09, 2024)
- Created VERSION, Created CHANGELOG.md, Bumped to 0.0.1
- wip
- Implementation de spatie Query Builder dans le UserController
- wip API
- Modification du livret de transformation et du service ParcoursService pour permettre l'emploi de classes dynamiques dans les modules FcmCentral et FcmUnite.
- Refactoring des use du service Parcours
- Adaptations pour gérer plusieurs parcours simultanément.
- Séparation des events par modules.
- wip affichage utilisateur, choix des parcours attribues
- wip events, actions, user interface
- wip.
- Ajout du widget de changement de panneau au panneau FcmCentral
- wip sur API pour historique utilisateur et parcours serialises
- Conversion du modele UserParcours en modele commun.
- La ressource Filament doit faire appel au modele du module.
- Le modele ParcoursSerialise etend le modele commun et redefinit son prefixe de table.
- Conversion des migrations parcours serialises et user_parcours pour faire appel à la definition commune dans FcmCommun
- Ajour de la logique d'autorisation a l'action de figement des parcours.
- Ajout du modele StoredEvent et de la migration associee, qui etend la migration du module FcmCommun
- Ajustement de l'action Figer pour contraindre les dates de validité de la nouvelle version figée.
- Ajout de la possibilite de figer les parcours en parcours serialises. Ajustement automatique de la date de fin de validité des versions précédentes à la veille du jour d'entrée en vigueur du nouveau parcours sérialisé.
- Retrait du bouton creer pour les ParcoursSerialises. Ajout de l'action "Figer" à la page Editer de la ressource Parcours.
- wip validation et devalidation des elements du parcours
- Creation du modele de parcours serialise et et de la ressource Filament pour les gerer.
- Ajout d'un menu et d'un lien pointant vers le panneau Filament.
- wip serialization parcours
- wip serialization pour vue3-treeview
- Adaptation du modele de donnees aux besoins de la FCM.
- Commande de test elementaire pour la serialisation et la deserialisation
- Ajustement des gestionnaires de relation pour aligner les formulaires de creation et les tables
- Ajout des relations manager permettant de gérer la constitution des parcours.
- Occultation de l'id par défaut
- Creation des ressources associees aux elements constitutifs des parcours.
- Creation des controlleurs et requetes par defaut pour les modeles.
- Correction erreur de definition de la relation SavoirFaire Competence
- Configuration des modeles. Definition des relations. Configuration des factories.
- Creation des migrations pour les modeles des parcours et les tables pivots
- Ajout du slug du module comme prefixe de table en base
- Ajout du slug du module dans les url des routes api
- Reprise des travaux exploratoires
- Ajout d'un panel Filament par défaut.
- Ajout du prefixe de l'instance, mise a jour composer.json et ajout des parametres utilisateur dans config.php
- Initial commit after creation

