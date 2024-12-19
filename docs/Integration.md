# IDE Integration

## PhpStorm

1. After installing the package for your project, open the PhpStorm settings.
2. Navigate to `Language & Frameworks` -> `PHP` -> `Quality Tools` -> `PHP_CodeSniffer`.
3. Choose _"Local"_ in the _"Configuration"_ dropdown.
4. Click the _"..."_ button next to the dropdown. It will show a dialog where you need to specify the path for the PHP_CodeSniffer executable.
5. Open the file selection dialog, navigate to `vendor/bin/` in your project, and select `phpcs`. (On Windows, choose `phpcs.bat`.)
6. Click the _"Validate"_ button next to the path input field. If everything is working fine, a success message will be shown at the bottom of the window.
7. Still in the PhpStorm settings, navigate to `Editor` -> `Inspections`.
8. Type `codesniffer` in the search field before the list of inspections.
9. Select `PHP` -> `Quality Tools` -> `PHP_CodeSniffer validation`.
10. Enable it using the checkbox in the list, and click _"Apply"_.
11. Select _"PHP_CodeSniffer validation"_, click the refresh icon next to the _"Coding standard"_ dropdown on the right, and choose `Syde` (or `Syde-Core` or `Syde-Extra`).
12. If you don't see the standard, you may have to specify your custom ruleset file by selecting _"Custom"_ as standard and then use the _"..."_ button next to the dropdown.

Once the PhpStorm integration is complete, warnings and errors in your code will automatically be shown in your IDE.
