# TFA - AECB - backend
Projet github représentant la première moitié du projet de fin d'année de SGDB
Celui-ci contient la partie Backend du projet écrite en PHP, utilisant MySQL pour réaliser la DB.

## Procédure d'ajout d'une entity
1) Création de l'entity dans src/Domain
2) Définition de l'interface dans src/Domain/Repository
3) Implementation de l'interface dans src/Infrastructure/Persistence
4) Définition des UseCases dans src/UseCase
5) Controller dans src/Controller