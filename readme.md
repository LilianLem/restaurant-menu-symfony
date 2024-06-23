# Restaurant Menu Symfony

***Disclaimer:** at this time, only API part is developed. Core features are mostly finished, but they have not yet been deeply tested.\
PWA and Helm in this repository are still API Platform distribution defaults. Frontend app is planned for later.*

The goal of this project is to provide an **easy but complete menu service to restaurant owners**, so they can **create and manage their menus**, and **make them available to everyone** online.<br>
**This project is not an ecommerce service**, but is rather meant to be a hosting service of showcase "websites".

## Setup

- Clone the repository
- **If you want working emails** (for account verifying and password reset), create **.env.local in /api** directory, and set **MAILER_DSN** environment variable.
- Run ```docker compose build --no-cache``` and ```docker-compose up -d --wait``` in root directory to setup containers
- Run ```docker compose exec php php bin/console d:f:l --purge-exclusions=allergen -v``` to **generate data fixtures**

## Using the API

*If you want to view database content or structure, go to Adminer at https://localhost:8080.<br>
The default credentials are restaurant-menu-symfony / password .*

**Head to https://localhost/docs to access OpenAPI.**

When not connected, you can only execute those requests:
- **All requests under "Authentication" tag**
- **POST /users**
- **GET /allergens**
- All **GET /{resource}/{id}** except for User, and **only if the resource is "public"** (set as visible by its owner, as well as all its parent resources, and not in trash ; *see isPublic() methods in /api/src/Entity for more details*)

### Authentication

Under Authentication tag, **send a POST request to /token** with one of those emails :
   - user@rmsymfdev.tk
   - admin@rmsymfdev.tk
   - sadmin@rmsymfdev.tk

You can also choose any account created by fixtures viewable in Adminer.<br>
In dev environment, **the default password for all accounts is password**. 

Once the request is successful, you can retrieve auth token and refresh token in the response, but **this API is configured to also store them in cookies automatically**.

### Usage when authenticated

**Once logged, you have access to almost every route**, no matter what account type you chose, and most of public routes are now populated with more fields.

**If you're an admin, you can view and edit all resources**, except soft deleted ones.<br>
The only exception is that **an admin (not super-admin) cannot edit other admin users or add ROLE_ADMIN to others**.

**A simple user can execute every available POST request** except for User, and **it only sees owned resources, including in GET collection** requests that are automatically filtered.

### Technical/functional details

#### Entity relationships

- A **Restaurant** is linked to **a single User** and **multiple Menu** (through RestaurantMenu entities)
- A **Menu** is linked to **multiple Restaurant** and **multiple Section** (through MenuSection entities)
- A **Section** is linked to **a single Menu** *(can change in the future if needed by simply changing relations, other mechanisms are ready)* and **multiple Product** (through SectionProduct entities)
- A **Product** is linked to **multiple Section**, **multiple ProductVersion** and **multiple Allergen** entities

#### Ranked entities

**A ranking system is deployed** on Menu, Section, Product and ProductVersion entities (called "ranked entities").

For example, **when creating a Product, you have to choose the first Section** it will be linked to.<br>
In addition, **you can also specify what rank it will have in this Section**, therefore incrementing other Product entities ranks by one if already taken. If you don't, the rank will be 1 higher than the uppermost Product in the Section.

Ranks are stored in "ranking entities", which are usually join entities. "Ranked entities" can also be "ranking entities" for themselves.<br>
They are, respectively: RestaurantMenu, MenuSection, SectionProduct and ProductVersion.

**When PATCHing a ranking entity** (SectionProduct if we stay in the same example), if you change its rank, **ranking entity siblings' ranks will also be changed** if needed to make some space for it, because **two entities cannot have the same rank in one parent**.

#### Soft deletion

**Soft deletion is performed when sending DELETE requests on User, Restaurant and Menu entities.**<br>
Other DELETE requests (RestaurantMenu, MenuSection, Section, SectionProduct, Product and ProductVersion) are hard deleting entities with **orphan cascade deletion**.

**Soft deletion also occurs on linked elements**: for example, deleting a Menu will soft delete it, as well as RestaurantMenu, MenuSection, Section and SectionProduct entities referencing it directly or indirectly, and also Product entities if they now only have soft deleted parents.