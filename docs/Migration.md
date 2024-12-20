# Migration

If you want to migrate from the Inpsyde PHP Coding Standards (version 1 or 2) to the Syde PHP Coding Standards, there are a few things you may have to change in your codebase, that is, custom PHP_CodeSniffer ruleset files, or command-line arguments in scripts or CI workflows, or inline `phpcs:` annotations.

## Standards

The Inpsyde PHP Coding Standards were only available as `Inpsyde` and `Inpsyde-Templates`. The former is the full standard, including almost all custom rules, except for a few sniffs that are part of the newer `Inpsyde-Templates` standard.

If you are using `Inpsyde-Templates`, the new standard you want to use is `Syde-Templates`.

If you are using the full `Inpsyde` standard, you may want to start with the `Syde-Extra` standard, and adapt things as you see fit.

If you were using the `Inpsyde` standard, but actually turned off a lot of the more opinionated sniffs, or if you have been cherry-picking sniffs without using the full standard, you may want to start with the new ´Syde-Core` standard, which only contains sniffs (custom and third-party!) that we consider **required** for a modern WordPress project at scale that considers performance, security and other hard best practices.

## Sniff Names

All sniffs have been renamed. As mentioned before, the name of the standard for all sniffs is now `Syde` (previously `Inpsyde`). In addition to that, all sniffs have been structured into sub-standards or sniff categories, very much in line with how the PHP_CodeSniffer and most other third-party standards are structured.

Here is a map of the old sniff names to the new ones:

- `Inpsyde.CodeQuality.ArgumentTypeDeclaration` -> `Syde.Functions.ArgumentTypeDeclaration`
- `Inpsyde.CodeQuality.DisableCallUserFunc` -> `Syde.Functions.DisallowCallUserFunc`
- `Inpsyde.CodeQuality.DisableSerialize` -> `Syde.Classes.DeprecatedSerializeMagicMethod`
- `Inpsyde.CodeQuality.DisallowSerializeInterface` -> `Syde.Classes.DeprecatedSerializableInterface`
- `Inpsyde.CodeQuality.DisallowShortOpenTag` -> `Syde.PHP.DisallowShortOpenTag`
- `Inpsyde.CodeQuality.ElementNameMinimalLength` -> `Syde.NamingConventions.ElementNameMinimalLength`
- `Inpsyde.CodeQuality.EncodingComment` -> `Syde.Encoding.Utf8EncodingComment`
- `Inpsyde.CodeQuality.FunctionBodyStart` -> `Syde.Functions.FunctionBodyStart`
- `Inpsyde.CodeQuality.FunctionLength` -> `Syde.Functions.FunctionLength`
- `Inpsyde.CodeQuality.HookClosureReturn` -> `Syde.WordPress.HookClosureReturn`
- `Inpsyde.CodeQuality.HookPriority` -> `Syde.WordPress.HookPriority`
- `Inpsyde.CodeQuality.LineLength` -> `Syde.Files.LineLength`
- `Inpsyde.CodeQuality.NoAccessors` -> `Syde.Classes.DisallowGetterSetter`
- `Inpsyde.CodeQuality.NoElse` -> `Syde.ControlStructures.DisallowElse`
- `Inpsyde.CodeQuality.NoRootNamespaceFunctions` -> `Syde.Functions.DisallowGlobalFunction`
- `Inpsyde.CodeQuality.NoTopLevelDefine` -> `Syde.PHP.DisallowTopLevelDefine`
- `Inpsyde.CodeQuality.PropertyPerClassLimit` -> `Syde.Classes.PropertyLimit`
- `Inpsyde.CodeQuality.ReturnTypeDeclaration` -> `Syde.Functions.ReturnTypeDeclaration`
- `Inpsyde.CodeQuality.StaticClosure` -> `Syde.Functions.StaticClosure`
- `Inpsyde.CodeQuality.VariablesName` -> `Syde.NamingConventions.VariableName`
- `InpsydeTemplates.Formatting.AlternativeControlStructure` -> `Syde.ControlStructures.AlternativeSyntax`
- `InpsydeTemplates.Formatting.ShortEchoTag` -> `Syde.PHP.ShortOpenTagWithEcho`
- `InpsydeTemplates.Formatting.TrailingSemicolon` -> `Syde.Formatting.TrailingSemicolon`

In addition to the above list, the following Inpsyde sniffs have been replaced by third-party sniffs:

- `Inpsyde.CodeQuality.ForbiddenPublicProperty` -> `SlevomatCodingStandard.Classes.ForbiddenPublicProperty`
- `Inpsyde.CodeQuality.NestingLevel` -> `SlevomatCodingStandard.Complexity.Cognitive`
- `Inpsyde.CodeQuality.Psr4` -> `SlevomatCodingStandard.Files.TypeNameMatchesFileName`

## Sniff Codes

For some sniffs, also the available codes were changed.

- `Inpsyde.CodeQuality.EncodingComment.EncodingComment` -> `Syde.Encoding.Utf8EncodingComment.Found`
- `Inpsyde.CodeQuality.HookPriority.HookPriority` -> `Syde.WordPress.HookPriority.PHP_INT_MAX` or `Syde.WordPress.HookPriority.PHP_INT_MIN`
- `Inpsyde.CodeQuality.NestingLevel.High` -> `Syde.Metrics.NestingLevel.TooHigh`
- `Inpsyde.CodeQuality.NoAccessors.NoGetter` -> `Syde.Classes.DisallowGetterSetter.GetterFound`
- `Inpsyde.CodeQuality.NoAccessors.NoSetter` -> `Syde.Classes.DisallowGetterSetter.SetterFound`

## Automated Migration on the Command Line

In case you have a large codebase with many `phpcs` annotations, and you want to "upgrade" these annotations to point to the newer rule, you can do this on the command line.

### Checking the Status Quo

First, you may want to check where there is anything to replace. You can do this by using [`grep`](https://www.gnu.org/software/grep/manual/grep.html).

```shell
grep -nri -e 'Inpsyde.CodeQuality' -e 'InpsydeTemplates.Formatting' --exclude-dir="vendor" .
```

The above command assumes that you have navigated into some plugin or theme directory. Since all sniffs in the Inpsyde standard are placed in either the `Inpsyde.CodeQuality` or the newer `InpsydeTemplates.Formatting` substandard, the command is looking for these. Also, depending on your setup, there may be Composer-managed dependencies, so the command excludes the `vendor/` directory, if present.

Feel free to adapt the command as you see fit. The idea here is to get an understanding of how much and what there is to migrate.

### Replacing Inpsyde Rules

Once you know what you are working with, it's time to actually replace the old references with the correct new ones. You can do this by using [`sed`](https://www.gnu.org/software/sed/manual/sed.html).

Most probably, you will have to perform several different replacements in several files. One easy way to do this is by reading a list of `sed` commands from a script file and apply it to a file pattern.

Create a file `phpcs.sed` with the following content:

```
s/Inpsyde\.CodeQuality\.EncodingComment\.EncodingComment/Syde.Encoding.Utf8EncodingComment.Found/g
s/Inpsyde\.CodeQuality\.HookPriority\.HookPriority/Syde.WordPress.HookPriority.PHP_INT_MAX/g
s/Inpsyde\.CodeQuality\.NestingLevel\.High/Syde.Metrics.NestingLevel.TooHigh/g
s/Inpsyde\.CodeQuality\.NoAccessors\.NoGetter/Syde.Classes.DisallowGetterSetter.GetterFound/g
s/Inpsyde\.CodeQuality\.NoAccessors\.NoSetter/Syde.Classes.DisallowGetterSetter.SetterFound/g
s/Inpsyde\.CodeQuality\.ArgumentTypeDeclaration/Syde.Functions.ArgumentTypeDeclaration/g
s/Inpsyde\.CodeQuality\.DisableCallUserFunc/Syde.Functions.DisallowCallUserFunc/g
s/Inpsyde\.CodeQuality\.DisableSerialize/Syde.Classes.DeprecatedSerializeMagicMethod/g
s/Inpsyde\.CodeQuality\.DisallowSerializeInterface/Syde.Classes.DeprecatedSerializableInterface/g
s/Inpsyde\.CodeQuality\.DisallowShortOpenTag/Syde.PHP.DisallowShortOpenTag/g
s/Inpsyde\.CodeQuality\.ElementNameMinimalLength/Syde.NamingConventions.ElementNameMinimalLength/g
s/Inpsyde\.CodeQuality\.EncodingComment/Syde.Encoding.Utf8EncodingComment/g
s/Inpsyde\.CodeQuality\.ForbiddenPublicProperty/SlevomatCodingStandard.Classes.ForbiddenPublicProperty/g
s/Inpsyde\.CodeQuality\.FunctionBodyStart/Syde.Functions.FunctionBodyStart/g
s/Inpsyde\.CodeQuality\.FunctionLength/Syde.Functions.FunctionLength/g
s/Inpsyde\.CodeQuality\.HookClosureReturn/Syde.WordPress.HookClosureReturn/g
s/Inpsyde\.CodeQuality\.HookPriority/Syde.WordPress.HookPriority/g
s/Inpsyde\.CodeQuality\.LineLength/Syde.Files.LineLength/g
s/Inpsyde\.CodeQuality\.NestingLevel/SlevomatCodingStandard.Complexity.Cognitive/g
s/Inpsyde\.CodeQuality\.NoAccessors/Syde.Classes.DisallowGetterSetter/g
s/Inpsyde\.CodeQuality\.NoElse/Syde.ControlStructures.DisallowElse/g
s/Inpsyde\.CodeQuality\.NoRootNamespaceFunctions/Syde.Functions.DisallowGlobalFunction/g
s/Inpsyde\.CodeQuality\.NoTopLevelDefine/Syde.PHP.DisallowTopLevelDefine/g
s/Inpsyde\.CodeQuality\.PropertyPerClassLimit/Syde.Classes.PropertyLimit/g
s/Inpsyde\.CodeQuality\.Psr4/SlevomatCodingStandard.Files.TypeNameMatchesFileName/g
s/Inpsyde\.CodeQuality\.ReturnTypeDeclaration/Syde.Functions.ReturnTypeDeclaration/g
s/Inpsyde\.CodeQuality\.StaticClosure/Syde.Functions.StaticClosure/g
s/Inpsyde\.CodeQuality\.VariablesName/Syde.NamingConventions.VariableName/g
s/InpsydeTemplates\.Formatting\.AlternativeControlStructure/Syde.ControlStructures.AlternativeSyntax/g
s/InpsydeTemplates\.Formatting\.ShortEchoTag/Syde.PHP.ShortOpenTagWithEcho/g
s/InpsydeTemplates\.Formatting\.TrailingSemicolon/Syde.Formatting.TrailingSemicolon/g
```

You can store the file either in the current project directory, or somewhere else (e.g., your home directory).

With the above substitutions in place, you can utilize them like so:

```shell
sed -i -f /path/to/phpcs.sed -- <INPUTFILE>
```

`sed` supports one or more input file paths, so we can use `grep` again to retrieve the list of files.

```shell
sed -i -f /path/to/phpcs.sed -- $(grep -nrl -e 'Inpsyde.CodeQuality' -e 'InpsydeTemplates.Formatting' --exclude-dir="vendor" .)
```

The above command will first retrieve the list of files that include `Inpsyde.CodeQuality` or `InpsydeTemplates.Formatting`, ignoring the `vendor` directory, and then execute all substitution commands in the `phpcs.sed` file for each of the found files.

As before, feel free to adapt the command as necessary. For example, if you want to start with the `src` folder only, pass `src` instead of `.` as last argument to the nested `grep` command.

## What Else?

The above will cover all references of Inpsyde rules in your PHP code. However, there are still a few things you will have to do manually.

### Code References

In case you are currently extending or otherwise referencing `Inpsyde\CodingStandard`, `Inpsyde\Sniffs` or `InpsydeTemplates\Sniffs` classes, you will have to adapt this to the new structure. The new base namespace is `SydeCS\Syde`, but there are more changes to class and method names, as well as function signatures.

### PHP_CodeSniffer Standards

Wherever you were previously referencing the `Inpsyde` standard, you may want to change this to `Syde-Extra` now, and iterate on the sniffs included in your rulesets. Or, if you want to start with the set of minimum required rules, update to `Syde-Core` instead.

If you are using `InpsydeTemplates`, this would now be `Syde-Templates`.

### PHP_CodeSniffer Rulesets 

In addition to the changes to the rule names, there are also changes regarding sniff configuration.

#### PSR-4 Namespace Configuration

As mentioned before, the custom `Inpsyde.CodeQuality.Psr4` sniff has been replaced by the `SlevomatCodingStandard.Files.TypeNameMatchesFileName` third-party sniff. This sniff works slightly differently when it comes to configuration.

The `Inpsyde.CodeQuality.Psr4` sniff has a public `array` property, `$psr4`, that takes elements with the key being a namespace and the value being one or more pipe-separated relative paths. An example configuration looks like so:

```xml
<rule ref="Inpsyde.CodeQuality.Psr4">
	<properties>
		<property name="psr4" type="array">
			<element key="MyCompany\MyProject" value="src"/>
			<element key="MyCompany\MyProject\Tests" value="tests/src|tests/e2e|tests/unit"/>
		</property>
	</properties>
</rule>
```

Now, the `SlevomatCodingStandard.Files.TypeNameMatchesFileName` sniff also has an `array` property, `$rootNamespaces`, that takes elements that map from a relative path to a namespace.

An example configuration that matches the above behavior looks like so:

```xml
<rule ref="SlevomatCodingStandard.Files.TypeNameMatchesFileName">
	<properties>
		<property name="rootNamespaces" type="array">
			<element key="src" value="MyCompany\MyProject" />
			<element key="tests/src" value="MyCompany\MyProject\Tests" />
			<element key="tests/e2e" value="MyCompany\MyProject\Tests" />
			<element key="tests/unit" value="MyCompany\MyProject\Tests" />
		</property>
	</properties>
</rule>
```

#### Public Properties

The `Inpsyde.CodeQuality.DisableMagicSerialize` sniff has a public property `$disabledFunctions`. For the new `Syde.Classes.DeprecatedSerializeMagicMethod` sniff, this property has been renamed to `$deprecatedMethods`.

Both the `Syde.Functions.ArgumentTypeDeclaration` and the `Syde.Functions.ReturnTypeDeclaration` sniffs have two new public properties: `$allowedMethodNames` and `$defaultAllowedMethodNames`. The second one includes a pre-defined list of method names that will be ignored by the sniff, whereas the first property is intended to be used to customize sniff behavior. If you currently ignore or disable either of the two sniffs, maybe you can review the relevant code, and make use of the new properties. 
