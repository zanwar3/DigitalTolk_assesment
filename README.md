# Refactoring Process

## Controller.php
- This class serves as the foundation for all controllers. I have incorporated several universal methods that are utilized across all controllers.
- These methods include homogeneous response and error handling methods, which streamline the process of generating consistent responses and managing errors.
- I have added general validators to validate inputs and to remove code duplication.

## BookingController.php
- Added error handling.
- Suggestion: Validation can be changed to Laravel FormRequest for better maintainability.
- Updated the constructor so that it can utilize different repositories.
- Suggestion: As the structure was monolithic, it can be broken down into services for better modularity.

## BookingRepository
- The booking repository was complex and had a lot going on. I have deconstructed it and divided it into scope-specific classes.
- There was a lot of code duplication, and string literals were used instead of constants.
- The environment variables were used directly, rather than safe handling i.e., using config.

## General Thoughts
1. The code was not following SOLID principles.
2. The code was not following DRY principles.
3. The code was not following KISS principles.
4. The structure was monolithic, which can be broken down into services and repositories.
5. Although the code works, in order to reduce the complexity and to make it more maintainable, it needs to be refactored.
6. I have added some comments to make the code more readable and understandable.
7. Unhandled return types and exceptions are not handled properly.
8. Unreachable / dead code was found.

In summary, the code needs significant refactoring. The changes I've made so far are just to give an idea of how it can be done.

