# Roles et Permissions

Retrouvez les Roles et les Permissions standard sur FCM Central

## Roles

### Marin

Role : 
- fcmcentral::marin

Permission :
- fcmcentral::marin.index
- fcmcentral::widget.marin


### Apprenant

Role : 
- fcmcentral::apprenant

Permission :
- fcmcental::widget.marinFcm
- fcmcentral::bilan.voir
- fcmcentral::livret.voir
- fcmcentral::livret.telecharger
- fcmcentral::livret.proposer

Resource :
- Marin :  index, edit


### Mentor

Role : 
- fcmcentral::mentor

Permission :
- fcmcental::widget.mentor
- fcmcentral::bilan.voir
- fcmcentral::livret.voir
- fcmcentral::livret.telecharger
- fcmcentral::livret.proposer
- fcmcentral::recherche_annuaire

Resource :
- Marin :  index, create, edit


### Chef de Service

Role : 
- fcmcentral::chefdeservice

Permission :
- fcmcental::widget.chefdeservice
- fcmcentral::bilan.voir
- fcmcentral::livret.voir
- fcmcentral::livret.telecharger
- fcmcentral::livret.valider
- fcmcentral::recherche_annuaire

Resource :
- Marin :  index, create, edit


## Bord

Role : 
- fcmcentral::bord

Permission :
- fcmcental::widget.bord
- fcmcentral::bilan.voir
- fcmcentral::livret.voir
- fcmcentral::livret.telecharger
- fcmcentral::recherche_annuaire

Resource :
- Marin :  index


## Admin

Role : 
- fcmcentral::admin

Permission :
- fcmcental::widget.bord
- fcmcentral::bilan.voir
- fcmcentral::livret.voir
- fcmcentral::livret.telecharger
- fcmcentral::recherche_annuaire
- fcmcentral::livret.valider
- fcmcentral::livret.annuler
- fcmcentral::attribuer.note
- fcmcentral::attribuer.parcours
- fcmcentral::attribuer.mentor
- fcmcentral::attribuer.cohorte
- fcmcentral::marin.suivreEnFcm
- fcmcentral::parcoursSerialise.figer
- fcmcentral::droits.admin

Resource :
- Marin :  index
- annee, ['index', 'create', 'edit', 'delete', 'order']
- competence, ['index', 'create', 'edit', 'delete', 'order']
- domaine, ['index', 'create', 'edit', 'delete', 'order']
- savoirfaire, ['index', 'create', 'edit', 'delete', 'order']
- etat, ['index', 'create', 'edit', 'delete', 'order']
- cohorte, ['index', 'create', 'edit', 'delete', 'order']
- anneeSavoirfaire, ['index', 'create', 'edit', 'delete', 'order']
- note, ['index', 'create', 'edit', 'delete', 'order']
- objectif, ['index', 'create', 'edit', 'delete', 'order']
- parcours, ['index', 'create', 'edit', 'delete', 'order']
- parcoursSerialise, ['index', 'create', 'edit', 'delete', 'order']
- parcoursType, ['index', 'create', 'edit', 'delete', 'order']
- savoirfaireType, ['index', 'create', 'edit', 'delete', 'order']
- typeEtat, ['index', 'create', 'edit', 'delete', 'order']
- typeNote, ['index', 'create', 'edit', 'delete', 'order']
- marin, ['index', 'create', 'edit', 'delete', 'order']