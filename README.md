# PHP knihovna pro EET

[![Build Status](https://travis-ci.org/filipsedivy/PHP-EET.svg?branch=master)](https://travis-ci.org/filipsedivy/PHP-EET) [![Latest Stable Version](https://poser.pugx.org/filipsedivy/php-eet/v/stable)](https://packagist.org/packages/filipsedivy/php-eet) [![Total Downloads](https://poser.pugx.org/filipsedivy/php-eet/downloads)](https://packagist.org/packages/filipsedivy/php-eet) [![License](https://poser.pugx.org/filipsedivy/php-eet/license)](https://packagist.org/packages/filipsedivy/php-eet)

## Instalace

### Composer

Pro instalaci balíčku je nutné jej instalovat skrze [composer](https://getcomposer.org/).

```bash
composer require filipsedivy/php-eet
```

## Ukázka užití

Ukázky naleznete ve složce **examples/**.

Certifikát **EET_CA1_Playground-CZ00000019.p12** byl vydán pro účel testování Daňovou správou. Tento certifikát nepoužívejte pro ostrou verzi. Svůj certifikát si vygenerujete skrze rozhraní Daňové správy.

```php
use FilipSedivy\EET\Certificate;
use FilipSedivy\EET\Dispatcher;
use FilipSedivy\EET\Receipt;
use FilipSedivy\EET\Utils\UUID;

$certificate = new Certificate(__DIR__.'/EET_CA1_Playground-CZ00000019.p12', 'eet');
$dispatcher = new Dispatcher(Playground, $certificate);

$uuid = UUID::v4(); // Generování UUID

$r = new Receipt;
$r->uuid_zpravy = $uuid;
$r->id_provoz = '11';
$r->id_pokl = 'IP105';
$r->dic_popl = 'CZ1212121218';
$r->porad_cis = '1';
$r->dat_trzby = new \DateTime();
$r->celk_trzba = 500;

echo $dispatcher->send($r);
```

## Aktualizace

- 3.0.0
  - Vytvoření [wiki](https://github.com/filipsedivy/PHP-EET/wiki) systému  
  - Úprava licence z GNU GPL 3 na MIT
  - Zrušení instalace bez composeru - [vyjádření](https://github.com/filipsedivy/PHP-EET/wiki/Pro%C4%8D-byla-zru%C5%A1ena-mo%C5%BEnost-instalace-bez-composeru%3F)
  - Code review
  - Úprava issues (_počeštění_)
  - Vytvoření standardu pro issues, pull requesty, vývoj,...
- 2.0.0
  - Úprava načítání certifikátu (*nyní není třeba certifikát převádět, třída jej převede sama*)

## Pomoc a řešní chyb
V případě že potřebujete poradit, nebo při implementaci Vám třída zobrazuje chybu můžete využít následujících kontaktů.
Základní pomoc je poskytována zcela zdarma. V ostatních případech se řídí dle aktuální ceny, kterou naleznete ve [wiki](https://github.com/filipsedivy/PHP-EET/wiki/Zasl%C3%A1n%C3%AD-p%C5%99%C3%ADsp%C4%9Bvku#co-z-toho-nebudu-m%C3%ADt).

### Issues
Issues je systém veřejných hlášení chyb. V rámci veřejné podpory jsou k dispozici štítky **otázka** a **potřebuji pomoc**.
Před tím než vytvoříte issue, zkuste se podívat zda není problém již řešen nebo již vyřešen někým jiným. [Seznam problémů řešených i vyřešených](https://github.com/filipsedivy/PHP-EET/issues?q=label%3Aot%C3%A1zka+label%3A%22pot%C5%99ebuji+pomoc%22).

### Email
V případě že veřejné řešení problémů nepřipadá v úvahu, tak je možné mě kontaktovat na emailové adrese, kterou naleznete na mé osobní stránce https://filipsedivy.cz.

## Příspěvek
V případě že se vám knihovna líbí a je vám užitečná, můžete mi zaslat příspěvěk. Jak zaslat příspěvek a co z toho budete mít můžete nalést [ve wiki](https://github.com/filipsedivy/PHP-EET/wiki/Zasl%C3%A1n%C3%AD-p%C5%99%C3%ADsp%C4%9Bvku).

### Přispěvatelé
| Přispěvatel | Částka |
|-------------|--------|
| https://www.manvel.cz | 500 Kč |

## Screencasty

- Generování certifikátu pro EET - https://youtu.be/N5Cb9SqeP6g

## Odkazy
- etržby.cz - http://www.etrzby.cz/
- Finanční správa - http://www.financnisprava.cz/cs/financni-sprava/eet
- Daňový portál - https://adisspr.mfcr.cz/adistc/adis/idpr_pub/eet/eet_sluzby.faces
- Informace o EET z pohledu podnikání - http://www.jakpodnikat.cz/eet-elektronicka-evidence-trzeb.php

## Licence
MIT - https://opensource.org/licenses/MIT
