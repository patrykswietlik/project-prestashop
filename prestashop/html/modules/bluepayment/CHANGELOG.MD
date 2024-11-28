# Changelog
## [3.0.8] 12.11.2024
- Fixed a bug with changing order statuses

## [3.0.7] 27.09.2024
- Fixed a bug with changing order statuses
- Added message about unconfigured service 

## [3.0.6] 19.08.2024
- Fixed a bug with changing order statuses

## [3.0.5] 02.08.2024
- Added a new section: Services for You, aimed at supporting merchants in running an efficient store
- Redesigned the informational banner
- Fixed error GA4 on new version prestashop
- Added missing parameters to BLIK and GPay
- Fixed a bug that caused the incorrect payment method to be displayed in the module configuration
- Fixed error 500 occurring during payment synchronization and communication with Presta

## [3.0.4] 26.06.2024
- Fixed added status selection

## [3.0.3] 20.06.2024
- Fixed translated
- Added status selection

## [3.0.2] 26.03.2024
- Fixed payment reset when changing mode
- Removed Smartney payment
- Fixed payment sorting
- Fixed errors when saving configuration
- Promoting Alior payments

## [3.0.1] 12.01.2024
- Fixed configuration page

## [3.0.0] 29.12.2023
- Rebranding
- Improved code standard

## [2.9.0] 28.09.2023
- Rebranding
- Added Blik pay later

## [2.8.9] 28.08.2023
- Adaptation for PrestaShop 8.1
- Fixed synchronization of payment gateways
- Rebranding

## [2.8.8] 28.06.2023
- Added Spingo payment
- Adding the ability to configure the module without having to turn it on
- Fixed adding ga script
- Fixed blocked access to read logs

## [2.8.7] 28.04.2023
- Fixed display null after change payment method
- Fixed change order status when product out of stock
- Remove directory libs, added dependencies from directory libs to composer

## [2.8.6] 4.04.2023
- Adaptation for PrestaShop 8

## [2.8.5] 3.01.2023
- Added amplitude analytics events
- Added PayPo payment

## [2.8.4] 15.12.2022
- Fixed duplicate statuses
- Fixed refund order
- Fixed promo settings
- Added amplitude analytics events
- Improved code standard
- Changed directory sdk to libs

## [2.8.3] 17.11.2022
- Added Env settings
- Fixed error ITN
- Fixed a status change that occurred after a status change in some time after an order

## [2.8.2] 02.11.2022
- Change the display of information about an available module update
- Change the behavior of statuses when the product is out of stock
- Added amplitude analytics events
- Refactoring config constants
- Improved status changes when ordering multiple delivery methods

## [2.8.1] 18.10.2022
- Fixed blik status autoupdate
- Fixed blik analytics
- Fixed e-mail bluemedia statuses in prestashop < 1.7.7.4
- Fixed payment status in history order
- Fixed error displaying payments in checkout in older prestashop version

## [2.8.0] 30.09.2022
- Code refactoring
- Updated amplitude events
- Corrected translations

## [2.7.9] 8.09.2022
- Fixed error displaying payments in thecheckout module
- Fixed bug blocking modal display
- Code refactoring
- Ability to translate card payment method
- Updated amplitude events
- Fixed bugs main gateway redirect
- Corrected translations
- Adding namespace
- Improved code standards

## [2.7.8] 2.08.2022
- Fixed bugs main gateway redirect

## [2.7.7] 1.08.2022
- Added Google Analytics tracking
- Added Amplitude tracking
- Added promote channels
- Added display of tags promoting installments
- Modified sql queries
- Added support for payment channels from the Czech and Slovak regions
- Fixed bugs
- Czech and Slovak language added
- Added help tab in module administration

## [2.7.6] 9.06.2022
- Fixed bugs in older version prestashop

## [2.7.4] 2.05.2022
- Fixed multistore
- Changes backend options
- Change frontend design
- Refactoring the SDK connection
- Refactoring javascript classes and libraries
- Added backend helpers
- Changing the payment method selection
- Promotions of selected payment methods
- Increasing analytical capabilities by adding data collecting events
- Update vendors

## [2.7.3] 16.03.2022
- Fixed multistore
- Improved google analytics by increasing analytics events
- Refractorization of classes
- Added backend helpers
- Update vendors

## [2.7.2] 23.02.2022
- Fixed onepagecheckout redirect payments
- Added redirect options for BLIK and Google Pay
- Improved grid payments
- Update vendors

## [2.7.1] 09.02.2022
- Fixed duplicate payments
- Fixed issues with displaying some payment channels
- Added deletion of inactive payments assigned to the account

## [2.7.0] 23.01.2022
- Fixed a group creation error during module installation
- Fixed popup payments in backend
- Added ajax saved configuration
- Changed the way to check for new version availability via API
- Added ajax controller
- Changes in css file location

## [2.6.9] 21.01.2022
- A major redesign of the module management panel
- Grouping of payments
- Notification of availability of a new version

## [2.6.7] 15.11.2021
- Payment return communication added
- Changing the name of the module

## [2.6.6] 27.09.2021
- The security of the module has been improved

## [2.6.5] 7.09.2021
- Fixed payment form

## [2.6.4] 30.08.2021
- Fixed language problems
- Hide payments if only credit cards are available

## [2.6.3] 25.08.2021
- Fixed status installations when updating a module
- Improved payment status

## [2.6.2] 19.08.2021
- Using composer package manager for better organization
- Add autoloader class
- Fixed ITN problem
- Fixed payment buttons language
- Creation of native transactional messages
- Change of default settings of transaction statuses
- Change classes directory to 'src'
- Changing the location of payment methods. Payments are located in the main module configuration
- Improved appearance of customer-side payment methods

## [2.6.1] 21.04.2021
### Changed
- Min and max amount for Smarney payments

### Added
- Compatibility with PHP 7.0

## [2.6.0] 23.02.2021
### Added
- Another option to connect with paywall

## [2.5.1] 21.01.2021
### Fixed
- Wrong language after redirecting to paywall

## [2.5.0] 15.12.2020
### Added
- Additional information text on thank you page

## [2.4.0] 03.12.2020
### Added
- New Payment Channel - Smartney

## [2.3.4] 13.08.2020
### Fixed
- Google Pay mobile payments

## [2.3.3] 24.06.2020
### Fixed
- Typo in button text

## [2.3.2] 20.05-2020
### Fixed
- Duplicated order payment in admin panel

## [2.3.1] 07.04-2020
### Added
- Translations for redirect to BlueMedia paywall

### Fixed
- Redirect for payment channels with PSD regulations

## [2.3.0] 19-03-2020
### Added
- License blocks
- Language support
- English translations
- PSD 2 agreements for defined payment channels
- Monolog Logger

## Deleted
- Return types for compatibility with PHP 5.6
- ITN statuses logic after payment
