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
- `Inpsyde.CodeQuality.HookPriority` -> `Syde.WordPress.HookClosureReturn`
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
- `Inpsyde.Formatting.AlternativeControlStructure` -> `Syde.ControlStructures.AlternativeSyntax`
- `Inpsyde.Formatting.ShortEchoTag` -> `Syde.PHP.ShortOpenTagWithEcho`
- `Inpsyde.Formatting.TrailingSemicolon` -> `Syde.Formatting.TrailingSemicolon`

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
