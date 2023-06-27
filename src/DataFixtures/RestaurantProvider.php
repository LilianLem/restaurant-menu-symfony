<?php

namespace App\DataFixtures;

use Exception;
use Faker\Provider\Base;

class RestaurantProvider extends Base
{
    /**
     * Généré par ChatGPT
     * @var array<int, array{string, string}> $starter
     */
    protected static array $starter = [
        ["Croissant au jambon et fromage", "Délicieux croissant garni de fines tranches de jambon et de fromage fondant."],
        ["Escargots de Bourgogne", "Escargots cuits au beurre à l'ail et aux herbes, une spécialité de la cuisine française."],
        ["Soupe à l'oignon gratinée", "Soupe riche à l'oignon, gratinée avec du fromage fondu et des croûtons."],
        ["Carpaccio de bœuf", "Tranches fines de bœuf cru assaisonnées avec de l'huile d'olive, du citron, du parmesan et des herbes."],
        ["Assiette de charcuterie", "Assortiment de différentes charcuteries, telles que le jambon cru, le saucisson, le pâté et le chorizo, accompagné de cornichons et de pain."],
        ["Œufs mimosa", "Œufs durs coupés en deux, garnis de leur jaune mélangé à de la mayonnaise, et saupoudrés de persil haché."],
        ["Terrine de légumes", "Terrine froide à base de légumes variés, tels que les carottes, les courgettes et les poivrons, cuits au four avec des œufs et de la crème."],
        ["Salade de lentilles", "Salade de lentilles vertes ou du Puy, assaisonnée d'une vinaigrette à base de moutarde, d'échalotes et de vinaigre de vin."],
        ["Velouté de champignons", "Soupe onctueuse à base de champignons de saison, de crème fraîche, d'oignons et de bouillon de légumes."],
        ["Salade de tomates", "Tomates fraîches asaisonnées coupées en tranches ou en dés, accompagnées de mozzarella, de basilic et d'oignons rouges."],
    ];

    /**
     * Généré par ChatGPT
     * @var array<int, array{string, string}> $dish
     */
    protected static array $dish = [
        ["Coq au vin", "Poulet fermier mijoté dans du vin rouge avec des champignons, des oignons et des herbes aromatiques."],
        ["Bouillabaisse", "Soupe de poisson provençale riche en saveurs, préparée avec une variété de poissons et de fruits de mer."],
        ["Quiche lorraine", "Tarte salée garnie de lardons fumés, d'oignons et d'une onctueuse préparation à base d'œufs et de crème."],
        ["Magret de canard, sauce bigarade", "Magret de canard grillé servi avec une délicieuse sauce à l'orange amère."],
        ["Galettes complètes", "Crêpes salées à base de farine de sarrasin garnies d'œuf, de jambon, de fromage et de légumes."],
        ["Cassoulet toulousain", "Plat mijoté à base de haricots blancs, de confit de canard, de saucisses et de viandes."],
        ["Poulet rôti aux herbes de Provence", "Poulet entier rôti au four avec un mélange d'herbes aromatiques provençales."],
        ["Moules marinières", "Moules cuites dans un délicieux bouillon à base de vin blanc, d'ail, d'oignon et de persil."],
        ["Escalope de veau à la crème", "Escalope de veau tendre, servie avec une sauce onctueuse à la crème et aux champignons."],
        ["Choucroute alsacienne", "Plat traditionnel alsacien à base de choucroute fermentée, de saucisses et de viandes fumées."],
        ["Tartiflette savoyarde", "Plat gratiné à base de pommes de terre, de lardons, d'oignons et de reblochon, originaire de la région de Savoie."],
        ["Blanquette de veau", "Plat mijoté de veau tendre, accompagné d'une sauce crémeuse à base de bouillon et de légumes."],
        ["Bœuf bourguignon", "Morceaux de bœuf mijotés dans un délicieux mélange de vin rouge, de légumes et d'herbes."],
        ["Poulet basquaise", "Plat à base de poulet, de poivrons, de tomates, d'oignons et d'épices, typique de la cuisine basque."],
        ["Cheeseburger", "Pain brioché, steak haché, fromage, laitue, tomate, oignon, cornichon et sauce burger."],
        ["Pizza Margherita", "Pizza classique garnie de sauce tomate, de mozzarella, de basilic frais et d'huile d'olive."],
        ["Bobotie du Cap", "Plat sud-africain à base de viande hachée épicée, d'œufs et de lait, gratiné au four avec une garniture d'amandes et de raisins secs."],
        ["Paella valenciana", "Plat espagnol à base de riz safrané, de fruits de mer, de poulet, de chorizo et de légumes."],
        ["Couscous royal", "Semoule de couscous servie avec un assortiment de viandes (agneau, poulet, merguez) et de légumes dans une savoureuse sauce."],
        ["Curry indien", "Plat épicé d'origine indienne, composé de viande ou de légumes cuits avec un mélange d'épices et de sauce."],
        ["Pad thai", "Plat thaïlandais de nouilles sautées au wok avec des crevettes, du tofu, des œufs, des cacahuètes et des légumes."],
        ["Tajine marocain", "Plat mijoté composé de viande, de légumes et d'épices, cuit lentement dans un plat en terre cuite."],
        ["Biryani indien", "Plat à base de riz parfumé, de viande, de légumes, d'épices et de fruits secs, cuit lentement pour développer les saveurs."],
        ["Goulash hongrois", "Ragoût de bœuf mijoté avec des oignons, des poivrons et des épices, servi avec des nouilles ou des pommes de terre."],
        ["Schnitzel viennois", "Escalope de viande panée et frite, généralement à base de veau, accompagnée de pommes de terre ou de salade."],
        ["Fish and chips", "Poisson frit croustillant, servi avec des frites épaisses et de la sauce tartare, une spécialité anglaise."],
        ["Moussaka grecque", "Plat grec à base d'aubergines, de viande hachée, de tomates, de béchamel et d'épices, gratiné au four."],
        ["Saucisse de Toulouse", "Saucisse de porc fumée et épicée, originaire de la région de Toulouse, souvent servie avec des lentilles."],
        ["Lapin à la moutarde", "Morceaux de lapin mijotés dans une sauce crémeuse à la moutarde, accompagnés de légumes."],
        ["Cordon bleu", "Escalope de veau ou de poulet farcie de jambon et de fromage, panée et cuite au four."],
        ["Chili con carne", "Plat tex-mex épicé à base de viande de bœuf hachée, de haricots rouges, de tomates, de poivrons et d'épices."],
        ["Chili sin carne", "Variante végétarienne du chili con carne, sans viande mais avec des légumes, des haricots et des épices."],
        ["Risotto aux champignons", "Riz crémeux cuit lentement avec des champignons, du bouillon, du vin blanc, du parmesan et des herbes."],
        ["Bibimbap", "Plat coréen composé de riz, de légumes sautés, de viande marinée, d'un œuf et d'une sauce piquante."],
        ["Entrecôte grillée", "Entrecôte de bœuf tendre et juteuse, grillée à la perfection. Servie avec un choix de beurre d'échalotes maison ou une sauce poivre onctueuse."],
        ["Salade niçoise", "Salade composée de thon, de haricots verts, d'œufs durs, de tomates, d'oignons, de poivrons, d'olives et d'anchois. Assaisonnée d'une vinaigrette à l'huile d'olive."],
        ["Salade de chèvre chaud", "Salade composée de fromage de chèvre gratiné sur des toasts, de mesclun, de noix, de tomates cerises et d'une vinaigrette au miel. Un mélange parfait de textures et de saveurs."],
        ["Tartare de saumon", "Poisson cru, coupé en petits dés et assaisonné avec de l'huile d'olive, du citron, des herbes et des épices. Servi avec des toasts croustillants."],
        ["Bœuf Stroganoff", "Plat russe à base de fines tranches de bœuf sautées avec des oignons, des champignons, de la crème fraîche et du paprika."],
        ["Croque-monsieur", "Sandwich chaud composé de jambon, de fromage et de béchamel, grillé jusqu'à ce que le fromage fonde et que le pain soit croustillant."],
        ["Escalope de veau milanaise", "Escalope de veau panée, servie avec des spaghetti à la sauce tomate et garnie de copeaux de parmesan et de feuilles de basilic frais. Un plat italien savoureux."],
        ["Salade César", "Salade composée de laitue romaine, de poulet grillé, de croûtons, de parmesan râpé et d'une vinaigrette crémeuse à l'ail et aux anchois. Un grand classique des salades."],
        ["Quenelles de brochet sauce Nantua", "Boulettes de pâte à base de brochet, cuites à la vapeur et servies avec une sauce onctueuse à la bisque d'écrevisses. Un plat délicat et raffiné de la cuisine lyonnaise."],
        ["Tartare de bœuf", "Bœuf cru finement haché, assaisonné avec des condiments tels que la moutarde, les câpres, les oignons et les herbes."],
        ["Salade grecque", "Salade fraîche et colorée avec des tomates, des concombres, des oignons rouges, des olives, du fromage feta et une vinaigrette à l'huile d'olive et au citron. Un classique méditerranéen."],
        ["Tarte à l'oignon", "Tarte salée garnie d'une préparation d'oignons caramélisés, d'œufs, de crème fraîche et de lardons. Cuite au four pour obtenir une croûte dorée et une garniture fondante."],
        ["Pâtes carbonara", "Spaghetti accompagnés d'une préparation aux œufs, au parmesan et aux lardons. Un plat italien simple et délicieux."],
        ["Hachis Parmentier", "Gratin de viande hachée (généralement de bœuf) et de purée de pommes de terre. Un plat réconfortant et copieux."],
        ["Quiche aux légumes", "Tarte salée avec une garniture de légumes variés, tels que les courgettes, les poivrons, les champignons, les épinards et les tomates, mélangés à une crème aux œufs. Un plat végétarien savoureux."],
        ["Poulet tikka masala", "Plat indien à base de morceaux de poulet marinés dans un mélange d'épices (comme le garam masala), puis cuits dans une sauce crémeuse à la tomate. Servi avec du riz basmati et du pain naan."],
    ];

    /** @var array<int, array{string}> $sideDish */
    protected static array $sideDish = [
        ["Frites"],
        ["Ratatouille provençale"],
        ["Gratin dauphinois"],
        ["Gnocchi à la sauce tomate"],
        ["Gratin de courgettes"],
        ["Riz pilaf"],
        ["Salade verte"],
        ["Pommes de terre sautées"],
        ["Pâtes au parmesan"],
        ["Purée de pommes de terre"],
    ];

    /**
     * Généré par ChatGPT
     * @var array<int, array{string, string}> $dessert
     */
    protected static array $dessert = [
        ["Tarte Tatin", "Tarte renversée aux pommes caramélisées, servie tiède avec une boule de glace à la vanille."],
        ["Tiramisu", "Dessert italien à base de biscuits imbibés de café, de mascarpone crémeux et de cacao en poudre."],
        ["Crème brûlée", "Dessert français classique à base de crème vanille onctueuse, caramélisée à la surface."],
        ["Mousse au chocolat", "Dessert léger et aérien à base de chocolat fondu et de blancs d'œufs montés en neige."],
        ["Tarte au citron meringuée", "Tarte à base de crème au citron acidulée et de meringue légère et croustillante."],
        ["Ile flottante", "Dessert français composé de blancs d'œufs montés en neige, cuits à la vapeur et servis avec une crème anglaise."],
        ["Panna cotta aux fruits rouges", "Dessert italien à base de crème cuite et gélifiée, accompagnée d'un coulis de fruits rouges."],
        ["Mille-feuille", "Pâtisserie composée de fines couches de pâte feuilletée, de crème pâtissière et de glaçage au sucre."],
        ["Fondant au chocolat", "Moelleux au chocolat avec un cœur fondant, servi chaud avec de la crème glacée à la vanille."],
        ["Tarte aux fraises", "Tarte garnie de fraises fraîches et recouverte d'un nappage brillant pour un dessert fruité et léger."],
        ["Profiteroles au chocolat", "Petits choux garnis de crème pâtissière et servis avec une sauce au chocolat chaud."],
        ["Crêpes Suzette", "Crêpes flambées à l'orange avec une sauce au beurre, au sucre et au Grand Marnier."],
        ["Tarte aux pommes", "Tarte classique aux pommes, avec une croûte croustillante et une garniture fruitée et légèrement sucrée."],
        ["Café gourmand", "Assortiment de petites douceurs sucrées servi avec un café, idéal pour finir un repas en légèreté."],
        ["Poire Belle-Hélène", "Poire pochée dans un sirop parfumé, servie avec de la glace à la vanille et du chocolat chaud."],
        ["Clafoutis aux cerises", "Dessert traditionnel français à base de cerises recouvertes d'une pâte à crêpe et cuite au four."],
        ["Tarte au citron", "Tarte à base de crème citronnée acidulée, avec une croûte sablée et parfois de la meringue."],
        ["Tartelette aux fruits", "Mini-tartelettes garnies de fruits frais ou cuits, avec une crème pâtissière ou une gelée."],
        ["Baba au rhum", "Petit gâteau imbibé de sirop au rhum, souvent garni de crème chantilly ou de fruits."],
        ["Charlotte aux fraises", "Dessert à base de biscuits à la cuillère imbibés de sirop, entourant une crème et des fraises."],
        ["Tarte au chocolat", "Tarte riche et chocolatée, avec une base de biscuit et une ganache au chocolat."],
        ["Pêche Melba", "Dessert à base de pêches pochées, de glace à la vanille et de coulis de framboise."],
        ["Tarte aux myrtilles", "Tarte garnie de myrtilles juteuses et sucrées, avec une pâte croustillante et une légère crème amande."],
        ["Gâteau basque", "Gâteau traditionnel basque à base de pâte sablée et garni de crème pâtissière ou de confiture de cerises."],
        ["Mille-feuille aux framboises", "Pâtisserie composée de fines couches de pâte feuilletée, de crème pâtissière à la vanille et de framboises fraîches."],
        ["Tarte au chocolat et aux noisettes", "Tarte avec une croûte croustillante au chocolat et une garniture crémeuse aux noisettes, décorée de noisettes concassées."],
        ["Fraisier", "Gâteau aux fraises avec une génoise légère, une crème mousseline vanille et une garniture généreuse de fraises fraîches."],
        ["Panna cotta à la mangue", "Dessert italien à base de crème cuite et gélifiée, accompagnée d'un coulis de mangue sucré et acidulé."],
        ["Tartelette aux poires et aux amandes", "Mini-tartelettes aux poires juteuses et aux amandes effilées, avec une crème d'amandes onctueuse."],
        ["Crumble aux fruits rouges", "Dessert composé de fruits rouges cuits et recouverts d'une pâte croustillante à base de beurre, de farine et de sucre."],
        ["Tiramisu aux fruits rouges", "Variante du tiramisu traditionnel, avec des fruits rouges frais ou cuits, imprégnés de café et de mascarpone crémeux."],
        ["Tartelette au chocolat et au caramel", "Mini-tartelettes au chocolat noir intense et au caramel fondant, décorées d'une touche de fleur de sel."],
        ["Mousse au citron vert", "Dessert léger à base de crème fouettée et de jus de citron vert, avec une texture aérienne et un goût rafraîchissant."],
        ["Brioche perdue", "Tranches de brioche trempées dans un mélange de lait, d'œufs et de sucre, puis cuites à la poêle et servies avec des fruits frais et du sirop."],
        ["Charlotte au chocolat", "Dessert composé de biscuits à la cuillère trempés dans du sirop, entourant une mousse au chocolat et servie avec une sauce au chocolat."],
        ["Cannelés bordelais", "Petits gâteaux caramélisés à l'extérieur et moelleux à l'intérieur, parfumés à la vanille et au rhum."],
        ["Mille-feuille aux fraises", "Pâtisserie composée de fines couches de pâte feuilletée, de crème pâtissière à la vanille et de fraises fraîches."],
        ["Tartelette aux agrumes", "Mini-tartelettes garnies d'une crème d'agrumes légèrement acidulée, avec des quartiers d'orange et de pamplemousse."],
        ["Coulant au chocolat", "Gâteau au chocolat avec un cœur fondant, servi chaud pour révéler une cascade de chocolat liquide."],
        ["Tarte aux figues et au miel", "Tarte avec une base de pâte sablée, garnie de figues juteuses et sucrées, et arrosée de miel pour une touche d'élégance."],
        ["Charlotte aux framboises", "Dessert à base de biscuits à la cuillère trempés dans un sirop, entourant une mousse légère aux framboises et garni de framboises fraîches."],
        ["Tarte au caramel beurre salé et aux noix", "Tarte avec une base de pâte sablée, une garniture crémeuse au caramel beurre salé et aux noix caramélisées, pour une combinaison sucrée et croquante."],
        ["Tartelette aux fruits exotiques", "Mini-tartelettes garnies de fruits exotiques frais ou cuits, comme la mangue, l'ananas et la passion, pour une explosion de saveurs tropicales."],
        ["Opéra", "Gâteau classique composé de fines couches de biscuit joconde imbibé de café, de crème au beurre au chocolat et de ganache au chocolat."],
        ["Verrine aux fruits rouges", "Dessert en verrine avec des couches de fruits rouges frais, de crème fouettée légère et de biscuits émiettés pour un contraste de textures."],
        ["Pain perdu aux fruits rouges", "Tranches de pain trempées dans un mélange de lait, d'œufs et de sucre, puis cuites à la poêle et servies avec des fruits rouges frais et un filet de sirop."],
        ["Tarte poire chocolat", "Tarte avec une croûte croustillante au chocolat, une garniture onctueuse au chocolat et des poires fondantes pour une combinaison irrésistible."],
        ["Mousse au chocolat blanc", "Dessert léger à base de chocolat blanc fondu et de crème fouettée, pour une texture aérienne et une douceur délicate."],
        ["Tartelette au chocolat et à la framboise", "Mini-tartelettes avec une base de pâte sablée au chocolat, une ganache au chocolat noir intense et des framboises fraîches pour une combinaison de saveurs exquise."],
        ["Mousse au chocolat et à la menthe", "Dessert léger à base de chocolat noir fondant et de crème fouettée à la menthe, pour un mariage rafraîchissant de saveurs."],
    ];

    /** @var array<int, array{string, int}> $freshDrink */
    protected static array $freshDrink = [
        ["Coca-Cola", 320],
        ["Coca-Cola sans sucres", 320],
        ["Fanta Orange", 320],
        ["Schweppes nature ou agrumes", 300],
        ["Diabolo", 300],
        ["Orangina", 320],
        ["Ice Tea pêche", 300],
        ["Oasis Tropical", 320],
        ["Evian", 350],
        ["San Pellegrino", 350],
        ["Perrier", 340],
        ["Perrier rondelle", 350],
        ["Limonade", 280],
        ["Sirop à l'eau", 200],
        ["Jus de fruits", 300]
    ];

    /** @var array<int, array{string, int}> $hotDrink */
    protected static array $hotDrink = [
        ["Café", 150],
        ["Grand café", 270],
        ["Thé", 300],
        ["Décaféiné", 150],
        ["Grand décaféiné", 270],
        ["Cappuccino", 350],
        ["Chocolat chaud", 220],
        ["Grand chocolat", 310],
        ["Café crème", 180],
        ["Grand crème", 280]
    ];

    /** @var array<int, array{string, int, string}> $alcoholicDrink */
    protected static array $alcoholicDrink = [
        ["Kir", 300, "Cassis, mûre ou framboise"],
        ["Porto", 320, "Blanc ou rouge"],
        ["Muscat", 320, ""],
        ["Martini", 320, "Blanc ou rouge"],
        ["Martini Schweppes", 400, ""],
        ["Whisky", 500, "Label 5"],
        ["Whisky coca", 600, ""],
        ["Rhum orange", 600, ""],
        ["Pastis ou Ricard", 300, ""],
        ["Suze", 300, ""],
        ["Cognac", 500, ""],
        ["Calvados", 450, ""],
        ["Get 27/31", 400, ""],
        ["Malibu", 400, ""],
        ["Vin rouge", 1500, "La bouteille de 75 cL - Bordeaux 2018"],
        ["Vin blanc", 1800, "La bouteille de 75 cL - Chardonnay 2020"],
        ["Vin rosé", 1800, "La bouteille de 75 cL - Côtes de Provence 2021"],
        ["Champagne", 4000, "La bouteille de 75 cL - Premier Cru"],
        ["Stella Artois", 330, "25 cL - Bière blonde - Pression"],
        ["Leffe blonde", 430, "25 cL - Pression"],
        ["Secret des Moines", 480, "33 cL - Bière brune - Bouteille"],
        ["Kwak", 450, "33 cL - Bière ambrée - Bouteille"],
        ["Leffe Ruby", 300, "25 cL - Bouteille"],
        ["Leffe blonde sans alcool", 280, "25 cL - Bouteille"],
        ["Cidre brut", 800, "La bouteille de 75 cL"]
    ];

    /**
     * Généré par ChatGPT
     * @var array<int, array{string, int, string}> $alcoholicCocktail
     */
    protected static array $alcoholicCocktail = [
        ["Mojito", 800, "Rhum blanc, menthe fraîche, jus de citron vert, sucre de canne, eau gazeuse"],
        ["Cosmopolitan", 900, "Vodka, triple sec, jus de cranberry, jus de citron vert"],
        ["Margarita", 850, "Tequila, triple sec, jus de citron vert, sel"],
        ["Piña Colada", 850, "Rhum blanc, jus d'ananas, lait de coco"],
        ["Sex on the Beach", 900, "Vodka, liqueur de pêche, jus d'orange, jus de cranberry"],
        ["Bloody Mary", 950, "Vodka, jus de tomate, jus de citron, sauce Worcestershire, Tabasco, sel, poivre"],
        ["Mai Tai", 1000, "Rhum brun, rhum blanc, Amaretto, jus d'ananas, jus d'orange, sirop d'orgeat"],
        ["Caipirinha", 800, "Cachaça, citron vert, sucre de canne"],
        ["Old Fashioned", 1000, "Whisky, sucre, Angostura bitter, zeste d'orange"],
        ["Martini Dry", 900, "Gin, vermouth dry, olive verte"],
        ["Daiquiri", 850, "Rhum blanc, jus de citron vert, sucre de canne"],
        ["Long Island Iced Tea", 950, "Vodka, rhum blanc, tequila, gin, triple sec, jus de citron, Coca-Cola"],
        ["Manhattan", 950, "Whisky, vermouth doux, Angostura bitter, cerise à cocktail"],
        ["Negroni", 950, "Gin, vermouth rouge, Campari, zeste d'orange"],
        ["Sour Apple Martini", 900, "Vodka, liqueur de pomme verte, jus de citron vert"],
        ["White Russian", 900, "Vodka, liqueur de café, crème fraîche"],
        ["Sazerac", 1000, "Whisky, sucre, Peychaud's bitter, absinthe, zeste de citron"],
        ["Mint Julep", 950, "Bourbon, sucre, menthe fraîche, eau gazeuse"],
        ["Tom Collins", 850, "Gin, jus de citron, sirop de sucre, eau gazeuse"],
        ["Singapore Sling", 1000, "Gin, Cherry Heering, Bénédictine, jus de citron, sirop de grenadine, eau gazeuse"]
    ];

    public function starter(int $minPrice = 500, int $maxPrice = 900): ProductData
    {
        $this->checkPrices($minPrice, $maxPrice);

        $data = static::randomElement(static::$starter);
        return new ProductData($data[0], $this->randomPrice($minPrice, $maxPrice), $data[1]);
    }

    public function dish(int $minPrice = 1100, int $maxPrice = 2000): ProductData
    {
        $this->checkPrices($minPrice, $maxPrice);

        $data = static::randomElement(static::$dish);
        return new ProductData($data[0], $this->randomPrice($minPrice, $maxPrice), $data[1]);
    }


    public function sideDish(?int $minPrice = null, ?int $maxPrice = null): ProductData
    {
        if(!is_null($minPrice) && !is_null($maxPrice)) {
            $this->checkPrices($minPrice, $maxPrice);
        } elseif(is_null($minPrice) xor is_null($minPrice)) {
            throw new Exception("Erreur : minPrice et maxPrice doivent avoir le même type (int ou null)");
        } else {
            $priceIsNull = true;
        }

        $data = static::randomElement(static::$sideDish);
        return new ProductData($data[0], isset($priceIsNull) ? null : $this->randomPrice($minPrice, $maxPrice));
    }

    public function dessert(int $minPrice = 500, int $maxPrice = 900): ProductData
    {
        $this->checkPrices($minPrice, $maxPrice);

        $data = static::randomElement(static::$dessert);
        return new ProductData($data[0], $this->randomPrice($minPrice, $maxPrice), $data[1]);
    }

    public function freshDrink(): ProductData
    {
        $data = static::randomElement(static::$freshDrink);
        return new ProductData(...$data);
    }

    public function hotDrink(): ProductData
    {
        $data = static::randomElement(static::$hotDrink);
        return new ProductData(...$data);
    }

    public function alcoholicDrink(): ProductData
    {
        $data = static::randomElement(static::$alcoholicDrink);
        return new ProductData(...$data);
    }

    public function alcoholicCocktail(): ProductData
    {
        $data = static::randomElement(static::$alcoholicCocktail);
        return new ProductData(...$data);
    }

    private function randomPrice(int $minPrice, int $maxPrice): int
    {
        return 50 * floor(mt_rand($minPrice, $maxPrice) / 50);
    }

    private function checkPrices(int $minPrice, int $maxPrice): void
    {
        if($minPrice < 0 || $maxPrice < 0) {
            throw new Exception("Erreur : minPrice et maxPrice ne peuvent pas être négatifs");
        }
        if($maxPrice < $minPrice) {
            throw new Exception("Erreur : le prix minimum doit être inférieur au prix maximum");
        }
    }
}