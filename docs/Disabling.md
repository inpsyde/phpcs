# Disabling or Excluding Rules

## Rules Tree

Sometimes it is necessary not to follow some rules. To avoid error reporting, it is possible to:

- disable rules from code, only in specific places;
- exclude rules for an entire project via configuration.

In both cases, it is possible to disable or exclude:

- a complete standard;
- a standard subset;
- a single sniff;
- a single rule.

These things are in a hierarchical relationship: _standards_ are made of one or more _subsets_, which contain one or more _sniffs_, which in turn contain one or more _rules_.

## Excluding Rules via Configuration File

Rules can be excluded for the entire project by using a custom `phpcs.xml` file, like this:

```xml
<?xml version="1.0"?>
<ruleset>

    <rule ref="Syde-Extra">
        <exclude name="Syde.Classes.DisallowGetterSetter" />
    </rule>

</ruleset>
```

In the example above, the `Syde.Classes.DisallowGetterSetter` sniff (and all the rules it contains) has been excluded.

By using `Syde.Classes` instead of `Syde.Classes.DisallowGetterSetter`, one would remove the entire `Syde.Classes` standard subset (sometimes also referred to as "sniff category"), whereas using `Syde.Classes.DisallowGetterSetter.SetterFound` would remove this one rule only, but no other rules in the `Syde.Classes.DisallowGetterSetter` sniff.

## Disabling Rules via Code Comments

Disabling a rule/sniff/subset/standard only for a specific file or a part of it can be done by using special `phpcs` annotations/comments. For example, `// phpcs:disable`, followed by an optional name of a standard/subset/sniff/rule, will disable the standard/subset/sniff/rule for the rest of the file:

```php
// phpcs:disable Syde.Classes.DisallowGetterSetter
```

Using `// phpcs:disable` without a rule/sniff/subset/standard will turn off PHP_CodeSniffer entirely (again, for the rest of the current file).

For more information about ignoring files, refer to the official [PHP_CodeSniffer Wiki](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Advanced-Usage#ignoring-parts-of-a-file).
