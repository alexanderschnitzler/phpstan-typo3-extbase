# PHPStan for Extbase

This package provides a couple of stubs and services to make your life easier when working with PHPStan and Extbase.

## Examples

```php
class Item extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {}

class ItemRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {}

/**
 * @template TEntityClass of Item
 * @template-extends Repository<TEntityClass>
 */
class ItemController
{
    private ItemRepository $itemRepository;

    public function listAction()
    {
        # this call does not longer generate a "call getFirst() on array" error
        $this->itemRepository->findAll()->getFirst();

        # PHPStan does now understand that this is a collection of Item classes.
        # This is made possible with @template and @template-extends annotations on your repositories
        $items = $this->itemRepository->findAll();

        foreach($this->itemRepository->findAll() as $item) {
            # PHPStan does now know that $item is an instance of Item which does not have method getFoo() defined.
            $item->getFoo();
        }

        # PHPStan does now know that $item can be null
        $item = $this->itemRepository->findByUid(1);

        #PHPStan does no longer report an error for such comparisons due to the former detection
        if ($item !== null) {
        }

        # PHPStan understands that $query deals with Item objects
        $query = $this->itemRepository->createQuery();

        # PHPStan does now know that execute returns a QueryResult<Item> with the argument set to false.
        $queryResult = $query->execute(false);

        # PHPStan does now know that execute returns an array of arrays (array<int,array<string,mixed>>) with the argument set to true.
        $array = $query->execute(true);
    }
}
```

## Installation

```shell
composer require schnitzler/phpstan-typo3-extbase
```

```
# phpstan.neon

includes:
	- vendor/schnitzler/phpstan-typo3-extbase/extension.neon
```

## Why yet another package?

Well, that's because it's easier for me to work on it with my own pace. Also, this package is not feature complete yet.
I am in contact and will continue to be with other package maintainers and maybe there will once be just one package for
all TYPO3 needs. But for now, this is the way to go.
