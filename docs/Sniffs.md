# Syde PHP Coding Standards Sniffs

Below you can find an alphabetical list of all custom sniffs included in the Syde PHP Coding Standards:

- [Classes](#classes)
  - [`Syde.Classes.DeprecatedSerializableInterface`](#sydeclassesdeprecatedserializableinterface)
  - [`Syde.Classes.DeprecatederializeMagicMethod`](#sydeclassesdeprecatedserializemagicmethod) ⚙️
  - [`Syde.Classes.DisallowGetterSetter`](#sydeclassesdisallowgettersetter) ⚙️
  - [`Syde.Classes.PropertyLimit`](#sydeclassespropertylimit) ⚙️
- [ControlStructures](#controlstructures)
  - [`Syde.ControlStructures.AlternativeSyntax`](#sydecontrolstructuresalternativesyntax)
  - [`Syde.ControlStructures.DisallowElse`](#sydecontrolstructuresdisallowelse)
- [Encoding](#encoding)
  - [`Syde.Encoding.Utf8EncodingComment`](#sydeencodingutf8encodingcomment) 🔧
- [Files](#files)
  - [`Syde.Files.LineLength`](#sydefileslinelength) ⚙️
- [Formatting](#formatting)
  - [`Syde.Formatting.TrailingSemicolon`](#sydeformattingtrailingsemicolon) 🔧
- [Functions](#functions)
  - [`Syde.Functions.ArgumentTypeDeclaration`](#sydefunctionsargumenttypedeclaration) ⚙️
  - [`Syde.Functions.DisallowCallUserFunc`](#sydefunctionsdisallowcalluserfunc)
  - [`Syde.Functions.DisallowGlobalFunction`](#sydefunctionsdisallowglobalfunction)
  - [`Syde.Functions.FunctionBodyStart`](#sydefunctionsfunctionbodystart) 🔧
  - [`Syde.Functions.FunctionLength`](#sydefunctionsfunctionlength) ⚙️
  - [`Syde.Functions.ReturnTypeDeclaration`](#sydefunctionsreturntypedeclaration) ⚙️
  - [`Syde.Functions.StaticClosure`](#sydefunctionsstaticclosure) 🔧
- [NamingConventions](#namingconventions)
  - [`Syde.NamingConventions.ElementNameMinimalLength`](#sydenamingconventionselementnameminimallength) ⚙️
  - [`Syde.NamingConventions.VariableName`](#sydenamingconventionsvariablename) ⚙️
- [PHP](#php)
  - [`Syde.PHP.DisallowShortOpenTag`](#sydephpdisallowshortopentag)
  - [`Syde.PHP.DisallowTopLevelDefine`](#sydephpdisallowtopleveldefine)
  - [`Syde.PHP.ShortOpenTagWithEcho`](#sydephpshortopentagwithecho) 🔧
- [WordPress](#wordpress)
  - [`Syde.WordPress.HookClosureReturn`](#sydewordpresshookclosurereturn)
  - [`Syde.WordPress.HookPriority`](#sydewordpresshookpriority)

⚙️: Sniffs marked with the ⚙️ symbol support customizing select properties.  
🔧: Sniffs marked with the 🔧 symbol support automatic fixing of coding standard violations.

---

## Classes

### `Syde.Classes.DeprecatedSerializableInterface`

> Report usage of deprecated `Serializable` interface.

This sniff triggers an error for classes implementing the deprecated `Serializable` interface.

The recommended way is to implement the `__serialize` and `__unserialize` magic methods instead, which were introduced in PHP 7.4. As of PHP 8.1, implementing the `Serializable` interface without the new magic methods is deprecated. This sniff reports **all** classes that implement the deprecated interface, no matter if they also have the new magic methods. The `Serializable` interface is planned to be removed from PHP with version 9.0.

---

### `Syde.Classes.DeprecatedSerializeMagicMethod`

> Report usage of deprecated serialize methods.

This sniff triggers an error for classes with the `__sleep` and `__wakeup` magic methods.

As of PHP 7.4, these methods are essentially deprecated, even though PHP does not signal an actual "Deprecated" message. The recommended way is to implement the `__serialize` and `__unserialize` magic methods instead, which were introduced in PHP 7.4.

---

### `Syde.Classes.DisallowGetterSetter`

> Report getter and setter methods.

This sniff triggers a warning for a getter or setter method.

By default, only public and protected methods are taken into consideration, but it is possible to include private methods via the `skipForPrivate` property:

```xml
<rule ref="Syde.Classes.DisallowGetterSetter">
    <properties>
        <property name="skipForPrivate" value="false" />
    </properties>
</rule>
```

The `skipForProtected` property can be used to also ignore protected methods, and only check public methods:

```xml
<rule ref="Syde.Classes.DisallowGetterSetter">
    <properties>
        <property name="skipForProtected" value="true" />
    </properties>
</rule>
```

Using PHP_CodeSniffer, it is not (easily) possible to determine what a method actually _does_. Consequently, this sniff reports all methods with a name that starts with `get` or `set`. If a method starts with _set_ or _get_ but is not an accessor, feel free to ignore or disable this sniff for it.

Setters are discouraged because alternative constructs and patterns like constructor injection and immutability are preferable. In the vast majority of cases, not using setters improves code design. That said, if you're sure that, for your case, no alternative is possible or desirable, feel free to ignore or disable this sniff.

Getters are discouraged because, very often, they are a symptom of bad design where object properties are "leaked" breaking encapsulation. By applying principles like ["Tell Don't Ask"](https://martinfowler.com/bliki/TellDontAsk.html), it is possible to improve code design without using getters.

This rule is also part of [Object Calisthenics](https://williamdurand.fr/2013/06/03/object-calisthenics/#9-no-getterssettersproperties), invented by Jeff Bay in his book [The ThoughtWorks Anthology](https://pragprog.com/book/twa/thoughtworks-anthology).

---

### `Syde.Classes.PropertyLimit`

> Report classes with an excessive number of properties.

This sniff triggers a warning for a class that exceeds the maximum allowed number of properties per class (default: 10).

The number of allowed properties per class can be configured via the `maxCount` property:

```xml
<rule ref="Syde.Classes.PropertyLimit">
    <properties>
        <property name="maxCount" value="20" />
    </properties>
</rule>
```

---

## ControlStructures

### `Syde.ControlStructures.AlternativeSyntax`

> Encourage usage of alternative syntax for control structures with inline HTML.

This sniff triggers a warning for each control structure with inline HTML in case it has an alternative syntax other than the regular curly-braces scope but is not using it. An example would be to use `if ($something) : /* ... */ endif;` instead of `if ($something) { /* ... */ }`.
Having nested PHP **and** HTML code, using alternative syntax makes the code more readable.

This rule is inspired by [`Universal.ControlStructures.DisallowAlternativeSyntaxSniff`](https://github.com/PHPCSStandards/PHPCSExtra/blob/ed86bb117c340f654eab603a06b95a437ac619c9/Universal/Sniffs/ControlStructures/DisallowAlternativeSyntaxSniff.php), but pretty much does the complete opposite.

---

### `Syde.ControlStructures.DisallowElse`

> Report usage of "else".

This sniff triggers a warning for each `else` keyword. Using early return or parameterizable return statements is much more preferable.

This rule is also part of [Object Calisthenics](https://williamdurand.fr/2013/06/03/object-calisthenics/#2-dont-use-the-else-keyword) invented by Jeff Bay in his book [The ThoughtWorks Anthology](https://pragprog.com/book/twa/thoughtworks-anthology).

---

## Encoding

### `Syde.Encoding.Utf8EncodingComment`

> Report usage of outdated UTF-8 encoding declaration comment.

🔧: This sniff supports automatic fixing.

This sniff triggers a warning for an outdated UTF-8 encoding declaration comment, `-*- coding: utf-8 -*-`.

---

## Files

### `Syde.Files.LineLength`

> Report lines with an excessive length.

This sniff triggers a warning if a line is longer than the allowed maximum length (default: 100 characters).

There are three exceptions:

- Lines that contain long strings used in WordPress translation functions are not reported; splitting the text would be against the WordPress Coding Standards.
- Lines that contain long single words, for example, URLs; it does not make sense to split a single word in multiple lines.
- Lines in inline HTML with a single HTML attribute that is longer than the allowed line length; while an HTML element with multiple attributes can be written with one attribute per line, it wouldn't change anything if a single attribute is already longer than the limit.

The maximum length can be configured via the `lineLimit` property:

```xml
<rule ref="Syde.Files.LineLength">
    <properties>
        <property name="lineLimit" value="120" />
    </properties>
</rule>
```

---

## Formatting

### `Syde.Formatting.TrailingSemicolon`

🔧: This sniff supports automatic fixing.

> Report trailing semicolon before closing PHP tag.

This sniff triggers a warning for a trailing semicolon right before a closing (inline) PHP tag.

While you can use this sniff in any context, it is usually part of rules specific to template and view files.

---

## Functions

### `Syde.Functions.ArgumentTypeDeclaration`

> Report incorrect, incomplete or missing argument type declarations.

This sniff triggers a warning for function arguments that are missing their type declaration.

There are some exceptions:

- `ArrayAccess` methods;
- magic methods;
- PHP native double underscore method;
- WordPress hook callbacks;
- select PHP methods: `seek`, `unserialize`.
 
Via the `allowedMethodNames` property, you can overwrite the list of method names that allowed to bypass the argument type checks:

```xml
<rule ref="Syde.Functions.ArgumentTypeDeclaration">
    <properties>
        <property name="allowedMethodNames" type="array">
            <element value="seek" />
        </property>
    </properties>
</rule>
```

You can also specify **additional** method names that are to be allowed:

```xml
<rule ref="Syde.Functions.ArgumentTypeDeclaration">
    <properties>
        <property name="additionalAllowedMethodNames" type="array">
            <element value="process" />
        </property>
    </properties>
</rule>
```

---

### `Syde.Functions.DisallowCallUserFunc`

> Report usage of `call_user_func` and `call_user_func_array`.

This sniff triggers a warning for `call_user_func` and `call_user_func_array`.

Variable function names make it hard to trace the usage and definition of a function. For consistency, calling a variable function should be done using the newer version of the syntax, that is, directly calling the variable function, and not pass it as an argument to `call_user_func` or `call_user_func_array`.

---

### `Syde.Functions.DisallowGlobalFunction`

> Report function definitions in the global space.

This sniff triggers an error for global function declarations.

User-defined functions should be declared in well-defined namespaces, and not in the global space. This helps structure code, and at the same time future-proofs your code in case new global functions get added to PHP (e.g., `str_starts_with` introduced in PHP 8.0).

---

### `Syde.Functions.FunctionBodyStart`

> Report missing/extra blank lines before the function body.

🔧: This sniff supports automatic fixing.

This sniff triggers a warning if a function does not have a blank line between signature and function body.

For functions with a single-line signature, it is possible to split the opening curly brace on the next line and then not have an additional blank line after:

```php
function foo(string $foo, string $bar): bool
{
    echo $foo . $bar;

    return true;
}
```

This is optional, and you may want to move the curly brace up and add a blank line after the signature. However, if you do place the curly brace in a new line, you must not have a blank line after it.

---

### `Syde.Functions.FunctionLength`

> Report functions with an excessive length.

This sniff triggers an error if a function is longer than the maximum allowed length (default: 50 lines).

By default, the following is ignored when counting lines:
 
- blank lines;
- comments;
- doc block **inside** function block.

The maximum allowed length can be configured via the `maxLength` property:

```xml
<rule ref="Syde.Functions.FunctionLength">
    <properties>
        <property name="maxLength" value="20" />
    </properties>
</rule>
```

It is also possible to include in the counting what's normally excluded via the properties:

- `ignoreBlankLines`
- `ignoreComments`
- `ignoreDocBlocks`

```xml
<rule ref="Syde.Functions.FunctionLength">
    <properties>
        <property name="ignoreBlankLines" value="false" />
        <property name="ignoreComments" value="false" />
        <property name="ignoreDocBlocks" value="false" />
    </properties>
</rule>
```

---

### `Syde.Functions.ReturnTypeDeclaration`

> Report incorrect, incomplete or missing return type declarations.

This sniff triggers an error if:

- declared return type is non-void, but void `return;` found;
- declared return type is either `null` or `void`, but incompatible non-void return statement found;
- declared return type does not include `null`, but `return null` found;
- declared return type does not include `void`, but void `return;` found;
- non-empty return type declared, but `return null` found;
- non-empty return type declared, but void `return;` found;
- declared return type does not include `Generator`, but `yield` found in function body;
- declared return type includes `Generator`, but no `yield` found in function body;
- declared return type includes `Generator`, but multiple return statements found in function body.

If no error is triggered as per the above list, this sniff triggers a warning if there is no return type declared.

There are some exceptions:

- `ArrayAccess` methods;
- magic methods;
- PHP native double underscore method;
- WordPress hook callbacks;
- select PHP methods: `count`, `current`, `getChildren`, `getInnerIterator`, `getIterator`, `key`, `valid`.

Please note that we are fully aware that 100% strictly typed code in PHP is rarely possible, feel free to ignore/disable the rule when any alternative is worse.

Via the `allowedMethodNames` property, you can overwrite the list of method names that allowed to bypass most of the return type checks:

```xml
<rule ref="Syde.Functions.ReturnTypeDeclaration">
    <properties>
        <property name="allowedMethodNames" type="array">
            <element value="getInnerIterator" />
            <element value="getIterator" />
        </property>
    </properties>
</rule>
```

You can also specify **additional** method names that are to be allowed:

```xml
<rule ref="Syde.Functions.ReturnTypeDeclaration">
    <properties>
        <property name="additionalAllowedMethodNames" type="array">
            <element value="getAllowed" />
        </property>
    </properties>
</rule>
```

---

### `Syde.Functions.StaticClosure`

🔧: This sniff supports automatic fixing.

> Report closures that can be declared static.

If a closure does not contain any reference to `$this`, it can be declared `static`. This sniff triggers a warning for such a closure.

However, static closures cannot be bound, even if they don't reference `$this`. In case a closure that does not contain a reference to `$this` needs to be bound, this sniff would incorrectly require to make it static. To tell the sniff that a closure cannot be static, either use the custom `@bound` annotation, or add a `@var` annotation for `$this` (e.g., `@var SomeClass $this`).

For the following code, the sniff will not trigger any warnings:

```php
/** @bound */
$a = function () {
    return 'Foo';
};

/** @var Foo $this */
$b = function () {
    return 'Foo';
};

$foo = new Foo();

$a->call($foo);

Closure::bind($b, $foo)();
```

---

## NamingConventions

### `Syde.NamingConventions.ElementNameMinimalLength`

> Report element names with a length less than 3 characters.

This sniff triggers a warning for element names with a length that is less than the recommended minimum of 3 characters. There is a list of names that are allowed even if one or two characters long.

The recommended minimum length can be customized using the `minLength` property:

```xml
<rule ref="Syde.NamingConventions.ElementNameMinimalLength">
    <properties>
        <property name="minLength" value="5" />
    </properties>
</rule>
```

Via the `allowedShortNames` property, you can overwrite the list of allowed short names:

```xml
<rule ref="Syde.NamingConventions.ElementNameMinimalLength">
    <properties>
        <property name="allowedShortNames" type="array">
            <element value="x" />
            <element value="y" />
            <element value="db" />
            <element value="id" />
        </property>
    </properties>
</rule>
```

You can also specify **additional** element names that are to be allowed regardless of their length:

```xml
<rule ref="Syde.NamingConventions.ElementNameMinimalLength">
    <properties>
        <property name="additionalAllowedNames" type="array">
            <element value="x" />
        </property>
    </properties>
</rule>
```

---

### `Syde.NamingConventions.VariableName`

> Check variable and property names against the specified naming convention.

This sniff triggers a warning for all variable and property names that do not match the specified naming convention (`camelCase` (default) or `snake_case`).

To change the type to check from the default `camelCase` to `snake_case`, use the `checkType` property:

```xml
<rule ref="Syde.NamingConventions.VariableName">
    <properties>
        <property name="checkType" value="snake_case" />
    </properties>
</rule>
```

By default, the sniff checks variables and class properties. It is possible to ignore either local variables or class properties respectively via the `ignoreLocalVars`
and the `ignoreProperties` property, respectively.

```xml
<rule ref="Syde.NamingConventions.VariableName">
    <properties>
        <property name="ignoreLocalVars" value="true" />
        <property name="ignoreProperties" value="true" />
    </properties>
</rule>
```

It is also possible to ignore user-defined names via the `ignoredNames` property:

```xml
<rule ref="Syde.NamingConventions.VariableName">
    <properties>
        <property name="ignoredNames" type="array">
            <element value="ALLOWED_ALL_CAPS" />
            <element value="allowed_snake" />
        </property>
    </properties>
</rule>
```

Please note that PHP super global variables and WordPress global variables are always ignored.

---

## PHP

### `Syde.PHP.DisallowShortOpenTag`

> Disallow short open PHP tag, while allowing the short echo tag.

This sniff triggers a warning if a PHP short open tag is encountered.

This sniff extends the `Generic.DisallowShortOpenTag` sniff. However, unlike the original, it allows the usage of short open tags with echo (`<?=`).

---

### `Syde.PHP.DisallowTopLevelDefine`

> Report usage of `define` where `const` is preferable.

In PHP, there are two ways to define global/namespaced constants (i.e., constants that are not class constants):

- `const`
- `define`

`define` is a function that is executed at runtime, whereas `const` is a language construct that is parsed at "compile time", or in other words: when PHP code is converted in bytecode **before** it is executed.

Besides the usual differences between functions and language constructs, being parsed at compile time allows constants defined by `const` to be cached via [OPcache](https://www.php.net/manual/en/book.opcache.php) or via [PHP 7.4+ preloading](https://wiki.php.net/rfc/preload).

Being parsed at compile time also means that `const` can be used in constructs that depend on runtime, for example, conditionals.

Overall, using `const` is preferable and should be the default way to define constants, where possible.

---

### `Syde.PHP.ShortOpenTagWithEcho`

🔧: This sniff supports automatic fixing.

> Encourage usage of short open PHP tag with echo for single-line output.

This sniff triggers a warning if a single-line echo statement is using the regular PHP open tag (`<?php echo`) instead of the short open tag with echo (`<?=`).

---

## WordPress

### `Syde.WordPress.HookClosureReturn`

> Ensure that action callbacks do not return, while filter callbacks always return something.

This sniff triggers an error if:

- a closure used as a WordPress action callback returns something;
- a closure used as a WordPress filter callback does not return something.

---

### `Syde.WordPress.HookPriority`

> Report usage of `PHP_INT_MAX` and `PHP_INT_MIN` as hook priority.

This sniff triggers a warning if:

- `PHP_INT_MAX` is used as priority for `add_filter`;
- `PHP_INT_MIN` is used as priority for `add_action` or `add_filter`.
