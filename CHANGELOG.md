## 0.2.15 (September 23, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.14 –> 0.2.15
- Implements art #967298 : modification des css car bug afficahge sur les puces et liste ( tailwind soucis)
- Implements art #967298 :  Modification  des Notes, amelioration  UX/UI depuis ajout Note Modal , modification du templates fiche Bilan, fiche bilan rajout des permissions document annexes, modif Roles seeder,  modifcation doc

## 0.2.14 (September 22, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.13 –> 0.2.14
- implements art #967298 : Modification des notes

## 0.2.13 (September 18, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.12 –> 0.2.13
- Changement du midleware d'authentification du panneau pour éviter les 401 lors d'un accès non authentifié et bien renvoyer l'utilisateur vers la page de login.

## 0.2.12 (September 03, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.11 –> 0.2.12
- Implements art  #945114 : Modification  et guide uitlisateurs des nouvelles modifications  de la release
- Implements art #945114 : Augmentation de la taille des logos pour le dasboard FcmCentral
- Implements art #945114 : Creation widget Presentation + Ajout Permission seeder +  Test Unitaire
- Develop OK ; petit bug suite a un conflit merge avec une page ( travaux sur une branche annexe en parallele)
- Implements art #944282 : Modification test unitaires suivant les nouvelles modifications des pages Suivi
- Implements art #944282 : Suppression Observer, rajout filtre unite sur selec marin mentors, modification du .md
- Implements art #944282 : Debut de creation de suivi  Mentors + test unitaires + Doc + debut verification Observers pour veification Marins
- FcmCentral : Rajout statut en fcm dans la requete pour le suivi stats des marins
- Implements art #927735 : ajout et modification doc techniques
- Impelements art #927735 : Modifcation et ajout doc technique
- Implements art #927735 : Correctif groupe sur Test Unitaire
- Implements art #927735 : Passage des documents annexe en S3-private creation nouvelle permission pour les documents annexe creation controller pour les acces Documents Annexe Modification route pour le controller DocuemenAnnexe Creation Test unitaire pour les document annexs ok modification Test unitaire pour Piece Jointe OK
- Implements art  #927735 :   MOdification S3-PRivate creation controller et route creation permission piece_jointe creation Nouvea Test Controller (ne marche pas encore)
- Implements art #926744 : Modification stored event en rajoutant ajout/supp pour les pieces jointes dans les logs
- Implements art #927335 : Ajout  Icon PDF ajout Method pour event LIsteners ajout/supp pour fiche Bilan modification methodes pour ajouter et supprimer piece jointe dans le Parcours Modification Doc Developpeur
- Implements art #927735 : rajout doc pour developpeur  ; FcmLight et FicheBilan
- Implement art #927735 : Refactorisation de la page FicheBilan modification model pour  suppression piece jointe S3 migration : ajout champ pour les  pieces jointes d'une fiche bilan marin creation doc pour fiche bilan developpeur modification listener pour ajout piece jointe
- Implements art #923628 :  Modification page Suivi stats ajout page XXX stat ajout permission rajout filtre FPS creation test unitiaires
- Implements art #874652 : modification pagination 25 au lieu de  50
- Implements art #874562 : Rajout bandeau Alert sur Livret FCM quand le marin est en GEL
- Implements art #874652 :  modification page ressource Ajout et modification filtre ajout Colonne modification export CSV
- Implements art #921511 : modification des migrations pour les Test Unitaires Resa
- Implements art  #905889 : suppression pdf sur S3
- Implements art #905889 : Creation function pour supp pdf dans s3
- Implements art #905889 : PDF sur S3 Public pour les les livrets mentor
- Terminer la conversion vers plusieurs mentors: supprime la colone mentor_id de la table fcm_marins. La migration de rollback selectionne le premier marin designe comme mentor et replace son id dans la table.

## 0.2.11 (July 15, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.10 –> 0.2.11
- Ajout des fichiers pour le pipeline de qualité du code sur PICSEL
- Correction bug dans le formulaire d'édition et de création des Domaines. Suppresion de la permission fcmentral::voir_tous_les_marins. Ajustement des règles d'affichage du badge dans le menu Gestion des Marins. Rassemblement des 3 migrations qlik. Mise à jour de la documentation des permissions et déplacement de la documentation technique dans un sous dossier "Pour Le Développeur".
- Ajustement pour garantir la compatibilité entre le listener et les évènements émis avant le passage à plusieurs mentors. Suppression des tests de migration inutiles. Passage à une migration de données en deux étapes: ici, on se contente de créer la nouvelle table et de copier les références aux mentors. La suppression de la colonne devenue inutile et des données qu'elle contient se fera lors de la montée vers la version suivante.
- Implements art #849337 : test unitaire eviter de  faire test de veif si mentor id exist
- Implement arts #849397 : Commit toutes les branches en 1 branche
- Implements art #782532 : modification QLicksense  export
- Implements art #807512 : corection group Test FcmCentral
- Implements art #807512 : Test unitaire  page stages ok
- Implements art #807512 : Creation page stats pour aide suivi FCM , export en csv data
- Implements art #806605 : doc permissions
- Implements art #806605 : Creation  revue des fonctions dans les models, etude des doublons et fonctions non utilisees a supprimer
- Implements art #803243 : Ajout Test Unitaire pour le Parcours Resource avec Relations Manager e+ figer
- Implement art #804831 : Suppression ancien SeederTestRole
- Implements art #804831 : correction test unitaire pour les mentors + modifcations autres
- Implements art #803243 : Modification relation ManyToMany MentorS + test Unitaires
- Implements art  #803243 : Modification bug Affectations, Competences avec code couleur refonte UUI/UX concernant SavoirfaireRelationsManager
- Implements art #782532 : Modification Qliksense avec ajout parcours JSON
- Implements art #782532 : correction de fautes orthographe
- Implements art #782532 : Creation d'une commande pour exporter les datas de la FCM pour BCF dans une table fcmcentral_qlicksense_exports

## 0.2.10 (June 10, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.9 –> 0.2.10
- Ajout d'une page d index dans la documentation.

## 0.2.9 (June 08, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.8 –> 0.2.9
- Modification du lien pointant vers la documentation pour éviter une erreur 403.
- Introduction de l'information de version du module dans le menu de l'utilisateur.

## 0.2.8 (April 15, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.7 –> 0.2.8
- Implements artifact #742300: retire les usages de la méthode hasRole dans les 3 resources qui s'en servent. Remplacement par un test sur user->admin.
- Ajustement du chemin de publication de la doc. Documentation générale du module Fcm Central

## 0.2.7 (April 09, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.6 –> 0.2.7
- Implements artifact #740077: la création d'un marin depuis la recherche annudef entraine l'association automatique avec l'utilisateur lorsqu'on en identifie un qui correspond.

## 0.2.6 (April 01, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.5 –> 0.2.6
- Implements art #727790 : Correction bug affichage  journal log: le lien vers le journal des évènements ne doit pas s'afficher si l'utilisateur n'a pas le droit d'y accéder.

## 0.2.5 (March 27, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.4 –> 0.2.5
- Correction de style avec php-cs-fixer.
- Ajustements divers: - retrait de la policy StoredEventPolicy: les StoredEvent n'ont pas vocation à être manipulés comme des ressources communes - modification de la gestion des droits d'accès à la page ListStoredEvent (journal est évènements): il suffit d'avoir la permission fcmcentral::voir_journal_des_evenements pour y accéder) - création d'un scope local pour les Stored Event afin de pouvoir filtrer les évènements en tenant compte de l'unité d'affectation du marin concerné - application de ce scope depuis la StoredEventRessource.
- Implements art #722830 : Page historique nouvelle version
- Implements art #722830 ; Creation resource StoredEvent journaux de logs  + policy
- Implements art #725422 : Modification + migrate Voir le commandant car BUG sur le chemin upload
- Implements art #725762 : Migration pour augmentation de nombre de caracteres dans le parcours pour les liblelle court et long Retouche des resources avec limite pour le libelle court a 500
- Implements art #725061 Ajout de nouvelles regles pour les Test Unitaires
- Implements art #696257 : classement des resources dans le group Catalogue
- Implements art #721560 : correction Bug savoir faire dans nouveau creation parcours
- Implements art #721560 : modification order court =>long
- Implements art #721560 : rajout filtre unite et lieu unite dans MarinResource
- Implements art #721560 ; refonte des calculs pour le taux avec les heures (prise encompte des horaires dans objectifs)
- Implements art #721560 : suppressions des limites caracteres dans les resources
- Implements art #721560 : Mise a jour RelationManager dans ParcoursResource pour rajout Heures + corection bug dans ObjectifResource
- Implements art #721560 : modification de la Ressource et du Model Objectifs
- Implements art #721560 : creation de la migration update pour les heures objectifs pensez a faire module:migrate FcmCentral
- Implements art #722821 : Modification du listener pour antidater une validation

## 0.2.4 (March 20, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.3 –> 0.2.4
- Implements art #721824 ; Correction de ChargeParcours suite a l'apparation d'un nouveau parcours Prendre en compte du parcoursType dans les competences
- Ajustements complémentaires des seeder restant: reprise des données depuis l'instance de production alfan.
- Déplacement et renommage du TruncateSeeder.
- Utilisation de la commande de chargement des parcours pour les tests de mise à jour du parcours du marin.
- Remontée du seeder des domaines au niveau du module. Ce seeder n'est pas spécifique au parcours DEASM.
- Implements art #709741 : Modification Seeder
- Implements artifact #716620: rajoute la permission fcmcentral::note.voir au seeder du module.
- Correction du chemin de publication de la documentation du module.

## 0.2.3 (March 13, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.2 –> 0.2.3
- Merge branch 'master' into tuleap-700370-bug-creation-parcours-impossibilite-de-relier-affectation-et-competences
- Implements artifact #713885: les autorisations d'accès aux pages LivretFcmLight et FicheBilan sont ajustées pour autoriser les accès à certains utilisateurs sans aucune permission en raison de leur inscription en FCM ou du fait qu'ils sont Mentor de quelqu'un en FCM. Un test unitaire vérifie ce point. Le widget Mentor affiche des icones cohérentes avec les icones déjà utilisées dans la liste des marins.
- Merge branch 'master' into tuleap-713885-ajuster-le-widget-marinssuperviseswidget-et-la-page-fichebilan
- Implements artifact #713887: la méthode getEloquentQuery de la MarinRessource est devenue inutile puisque le filtrage des Marins est fait dès le scope du modèle Marin du module RH.
- Merge branch 'master' into tuleap-713887-ajuster-ou-supprimer-la-methode-geteloquentquery-de-la-marinressource-de-fcmcentral
- Implements artifact #713874: le mentor a accès au livret de FCM de ses mentorés sans avoir besoin de permission particulière.
- Correction par rector for Filament v4.

## 0.2.2 (March 11, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.1 –> 0.2.2
- Quick fix: largeur du livret de FCM sur la page et retrait de l'action permettant d'ajouter un marin depuis la liste des marins.

## 0.2.1 (March 10, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.0 –> 0.2.1
- Inclusion des tests Mentor en provenance de FcmCommun. Ajustement de la méthode assignerMentor pour qu'elle accepte aussi bien un Marin de Fcm qu'un Marin de RH.
- Utilisation des actions et filtre dans la page MarinResource.
- Creation EnFcmFilter
- Creation AffecterMentorAction
- Creation AttribuerParcoursAction
- Creation CreeUneNoteAction
- Creation LivretPdfAction
- Creation JalonFcmAction
- Ceation ToggleFcmAction
- Création de la DownloadCsvAction

## 0.2.0 (February 19, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.1.2 –> 0.2.0
- Ajustements pour Filament v4 (donc Skeletor v4.0.0+)
- Ajout de la version dans module.json
- Merge branch 'filamentv4-upgrade'
- Ajustements sur les paramètres de compilation du CSS.

## 0.1.2 (February 16, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.1.1 –> 0.1.2
- Implements art #696979 : Rajout permission refuser pour la proposition mentoré (role Mentor)

## 0.1.1 (February 13, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.1.0 –> 0.1.1
- Modification de l'émission de l'évènement de mise en gel.
- Correction namespace du widget de documentation. Ajustement des tests: les pages de gestion des types de note, d'état et de parcours sont bloquées.
- Implements art #685746 : Modifications diverses sur les IHM et les permissions
- Ajustement de la publication de la documentation et du PDF destiné aux mentors + widget donnant le lien vers le PDF en question.
- Implements art #685746 modification bcf 8
- Implements artifact #690856: la page ListMarins ne génère plus d'exception quand on lui demande si canAccess avec un utilisateur invité. On introduit un test élémentaire pour éviter la régression.
- Nettoyage des tests. Retrait du fichier temporaire utilisé pour la sauvegarde et la restauration du parcours.
- En prévision d'une future refactorisation. Spécialisation des 2 méthodes de chargement: from_file ou from_content.
- La FicheBilan ne doit pas faire référence aux id qui ont disparu.
- Avec la nouvelle version sérialisée du parcours, on peut donc vérifier l'existence du parcours_type, et si besoin le recréér en base. Et on n'a plus besoin de chercher le savoir_faire_type par son code, mais on peut utiliser l'UUID.
- Il faut que la version sérialisé du parcours soit totalement auto porteuse. Donc le parcours_type doit être intégralement sérialisé, et pas seulement son UUID. Sinon, il faut aller chercher la définition du parcours type ailleurs. En en l'occurence, il n'y a pas d'ailleurs.
- On retire le seeder des UUID. Les données qui sont déjà dans la base de l'instance de production pourront être facilement sauvegardées, donc les objets auxquels on a rajouté une colonne UUID disposeront déjà d'un UUID généré par le serveur de BDD.
- Modification de la migration: au rajoute de la colonne UUID, on définit la valeur par défaut pour laisser le serveur de base de données nous générer automatiquement des UUID.
- Implements art #685517 : passer en full screen le panel FCM Central
- Implements art #680367 : travaux sur modification Parcours Rajout UUID  manquants (faire un migrate) Creation Seeder pour avoir des UUID Fixes Correction  Tests Suppresion migration pour creer table documentations (a supprimer dans la  PROD PhpmyAdmin)
- Implements artifact #682868: modifie le tag de publication de la documentation pour correspondre à la commande skeletor:publish-module-documentation
- Implements artifact #678635 Les widgets ne provoquent plus d'exception quand l utilisateur n est pas un marin ou n'a aucune autorisation. Meme chose avec la fiche bilan.
- Implements artifact #664288: introduit 2 commandes artisan permettant respectivement de sauvegarder la définition d'un parcours dans un fichier json, et de charger la définition d'un parcours depuis un fichier JSON. 2 tests unitaires sont inclus dans le commit.
- Implements art #677000 Correction Bug  Serialisation Parcours Marin - Retouche
- Implements art #677000 Correction Bug  Serialisation Parcours Marin
- Poursuite du nettoyage des vérifications de permissions dans le module. Nettoyage des tests verbeux.
- Implements artifact #458991 Prends en compte les informations de NID et de grade renvoyés par la recherche Annudef lors de la création de la fiche du Marin depuis la recherche Annudef dans FcmCentral.
- Implements artifact #672615 Ajuste les closures qui déterminent si les liens pointant vers la page "Mon livret FCM" sont cohérentes avec le fait que l'utilisateur courant a effectivement un livret de FCM.
- Implements art 668586 : Test Unitaire OK
- Implements art #668586 : Retouche des test Unitaires en fonction des dernieres modifications
- Implements art #668586 : modification Etats en Gel, retouche page fiche bilan, recherche bug Bu? suggestion ? non trouve encore
- Implements art #668586 : mise a jour event MarinRessource et Fiche Bilan filtre sur MarinResource

## 0.1.0 (January 13, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.10 –> 0.1.0
- Implements art #666617 : test unitaires OK
- Implements art #665480 : Suppression de ExportController dans Competence Resource pour exportation Data
- Implements art #665449  : Suppression de la page LivretMentorConsultation
- Implémentation des tests élémentaires sur les pages de base du module.
- Corrections syntaxiques, suppression de fichiers devenus inutiles.
- Réintroduction des menus d'accès direct, et ajustement des pages préférées.
- Implements art #662190  : Suite des Travaux Unitaires (encore des Reglages a effectuer)
- Implements art #663213 : Modification BCFet bug  mentor
- Implements art #662190 : Fin des modifcations des sseders Parcours DEASM test Unitaite : PagePanel et retouche PanelTest (pas fini)
- Implements art #661834  : Modification Commandant Escaffe
- Implements art #661551 : Debut des travaux test unitaire (ne marche pas encore, en pause car je dois retoucher partie BCF)
- Retrait de référence inutile.
- Retrait de la dernière référence à la classe FcmCentral Marin dans l'initialisation du LivretFcm.
- Implements art #660223 : Figer les uuid sur le Seeder DEASM
- Implements art #660180 : Debut de export Data en CSV depuis marin pour BCF (manque des champs a rajouter prochainement)
- Implements art #660180 : modification acces Admin pour le mot de passe dans Marins modification pour page LivretMentor (rajout des classes)
- Implements art #657999 : Travaux Correction savoirfaire bug 2.0
- Implements art #657999 : Travaux Correction savoirfaire bug
- Implements art #659711 : Correction Bug Savoirfaires-Objectifs
- Implements art #657999 : Travaux Correction savoirfaire bug 2.0
- Implements art #657999 : Travaux Correction savoirfaire bug
- Implements art #657999 : Travaux Modification BCF
- Ce commit ajuste la déclaration du livret de FCM pour lui passer les classes Marin et UserGeneratedEvent à utiliser ainsi que le préfixe des permissions (fcmcentral::).
- Correction Bug TypeEtat dans le Seeder Principal de FcmCentral
- Correction PSR-4 Composer dump-autoload ok
- Modification en vue du passage en production.
- Refonte page fiche Bilan , ajouts etats  , meilleure contraste dark nmode
- Correction bug affichage : SF identique mais type different, objectifs existant mais non affichés
- Correction bug redacteurNotesId
- Correction bug Notes Etats Marin
- Nettoyage des Models MArins et FcmMain , debut du menu Helper, widget test vue Roles Permissions Marin
- Refonte Page Annuaire Form
- Correction vue parcours du marin depuis dahboard Mentor ( etude des permissions pour differencier mentor et marin)
- Creation widgets pour marins et mentor : Travail fait vite fait pour la reunion de mardi 02/09 ; visuel page mentor depuis widget ne marche car ne recupere pas arguement dans page filament Relation a revoir entre Marins et FcmMAarins
- Correction typo dans une permissions. Rajout du seeder de la permissions de modification de la configuration du module.
- Création de nouvelles permissions pour réglementer l'emploi des fonctions du module. Rajout des vérifications de ces permissions dans les actions Filament correspondantes.
- Correction de typo pour la déclaration de la policy associé au modèle Cohorte. Correction de la référence au modèle Cohorte dans la CohorteResource. Création de la permission fcmcentral::recherche_annuaire pour pouvoir autoriser ou refuser l'accès la page de recherche dans l'annuaire Rajout de cette permission dans le role "superviseur_fcm"
- Projet Dashboard pour Marin 1.0
- Modif DataSeeder et MonlivretFCM Seeder ; il manquait un virgule LivretFCM : Si user pas de de parcours , on retournait une erreur lors affichage
- Pour éviter les exceptions lors du logout.
- Introduction de la page Mon Livret de FCM
- Introduction d'une policy pour le modèle Marin et seeder d'un role.

## 0.0.10 (August 25, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.9 –> 0.0.10
- Merge branch 'fldb-nouveau-livret'
- Adaptation du livret de FCM
- Création de quelques permissions utiles pour la gestion de la FCM
- Livret FCM : Event SavoirFaire
- PDF Livret v1
- Ajustement de la page de recherche dans l'annuaire pour utiliser les events.

## 0.0.9 (July 25, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.8 –> 0.0.9
- Ajustement pour la prise en compte des propositions, annulation de propositions. Ajout des tests unitaires correspondant.
- Ajustement du listener de proposition de validation d'element du parcours

## 0.0.8 (July 25, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.7 –> 0.0.8
- Merge remote-tracking branch 'origin/julien-adaptation-annulation-element-parcours'
- Implementation de l'annulation d'un element du parcours
- Rajoute du listener d'annulation à la liste des listeners des UserGeneratedEvent
- Commit NOT WORK
- Création du test pour la dévalidation d'un élément du parcours
- Réactivation de tous les tests
- Merge branch 'julien-recalcul-etat-parcours-apres-evenement-metier'
- Commit : Verification Etats Parents si Enfants tous cochés
- Préparation du travail de recalcul de parcours à chaque évènement

## 0.0.7 (July 23, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.6 –> 0.0.7
- La version modal pour la création ou l'édition d'un marin semble plus ergonomique.
- Corrections busgs résiduels après merge
- squash commit for sza modifications
- amélioration page activités du parcours
- Merge branch 'fldb-debut-documentation' into fldb-simplification-events
- Merge commit '98acb7bb6b6f42d7c98948213024d409939d5e24' into fldb-simplification-events
- Ajustement de listener pour mettre à jour le parcours du marin.
- Rajoute import manquant et retrait import en double.
- Commit  LivretFCM MAJ + Validation
- Retrait NavigationHelper du panneau FCM Central
- Hotfix: correction typo dans le livret de FCM.
- Correction type dans le livret de FCM. Création d'un test d'affichage du Livret pour assurer la non régression.
- Intégration des liens pointant vers la documentation et la documentation des api dans le menu utilisateur du panneau de FCM central.
- Débute l'implémentation de la documentation du module.
- Rajout de la déclaration de quelques pages préférées du module FcmCentral
- Retrait dump oubliés dans les tests.
- Complément de commentaires dans les listeners ayant à faire appels aux données de FCM du Marin pour faciliter le travail du développeur.
- Début de modification du listener ValidElementMarinParcoursListener pour le faire travailler sur les évènements conformes au dictionnaire des évènements v2. Travail à finaliser pour ce listener, et à réaliser pour les autres Listeners du livret de FCM.
- Rajout du listener ValidElementMarinParcoursListener aux listeners des UserGeneratedEvent
- Modification du Livret pour spécifier la classe d'évènements à utiliser.
- Correction de typo
- Retrait d'un dump oublié
- Début de simplification des évènements FCM. Reste notamment à traiter le cas du livret de transformation.
- Réintégration des migrations de moficiation du schéma de bdd dans les migrations initiales. Mise à jour des factory. Mise à jour des seeders du parcours DEASM. Intégration de tests unitaires pour vérifier la compatibilité entre les factory et le schéma de base.
- Implements art #480061 : Amelioration Test Unitaire pour EventTrigger
- Implements art #480061 : Modification Activites Savoirfaire pour le bug data en array
- Implements art #480061 : Modification Parcours
- Implements art #480061 : Optimisation Livret FCM
- Implements art #480061 : Livret FCM Refonte et verification Ensemble Parcours Ajout et modification des Pivots pour les liaisons (cote frontend) Creation Seeder Parcours Deasm
- Implements art #480061 : Livret FCM  Proposition Restore Reboot
- Implements art  #480061 : Livret FCM v3 (proposition)
- implements art #480061  : Livret FCM v2 (Validation, Annulation)
- Implements art #480061 : Mise a jour Livret FCM Gestion du parcours + sauvegarde event Difficulte sur les many to many avec ascendants et descendants mais c'est ok Toute la logique est mis dans le Livewire pour eviter d'inserer du jquery Soucis sur FcmCentral_stored car il cherche le uuid du Marin_Parcours Rafraichissement ne fonctionne pas apres mise a jour doit faire Annulation cas unique et Proposition && Annul_Proposition (juste event a construire) etude a venir sur la presentation du parcours Rajout champ supplementaire sur le Parcours Serialise
- Il faut préciser le panneau à utiliser pour les liens d'accès direct.

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

