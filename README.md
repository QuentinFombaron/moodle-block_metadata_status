![metadata_status_logo](https://i.imgur.com/vbb08cF.png)

# Moodle plugin « Metadata Status »

*Quentin Fombaron - April 17<SUP>th</SUP> 2020*

*Centre des Nouvelles Pédagogies (CNP\*), Univ. Grenoble Alpes - University of innovation*

This plugin is developed by Quentin Fombaron. It is initialy developped for [caseine.org](https://moodle.caseine.org). Do not hesitate to send your thinking and bug reports to the contact addresses bellow.

Contacts :
- [Quentin Fombaron](mailto:q.fombaron@outlook.fr)
- [Caseine](mailto:contact.caseine@grenoble-inp.fr)

\* *The CNP, set up as part of the IDEX Training component, brings together the DAPI, PerForm and IDEX support teams.*

## Table of content

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Configuration](#configuration)
    1. [Administration](#administration)
        1. [Metadata Status block configuration](#metadata-status-block-configuration)
        2. [Metadata tracking](#metadata-tracking)
        3. [Metadata status customization](#metadata-status-customization)
    2. [Block](#block)
    

## Requirements
- Moodle 3.9+ (`2020061500`)
- [Metadata plugin](https://moodle.org/plugins/local_metadata)

## Installation
Install the block content in `/blocks/metadata_status` directory.

## Usage
An icon indicates whether the module is shared (`1`), a progress bar with a threshold informs if the module metadata is sufficiently filled (`2`) and a percentage indicating the amount of metadata filled (`3`).

![metadata_status_progress](https://i.imgur.com/DA0xID8.gif)

Key figures in the block summarize the course metadata.

![metadata_satus_key_figures](https://i.imgur.com/vMMUf4J.png)

## Configuration

### Administration
#### Metadata Status block configuration
To track the module share, provide the metadata field `shortname`.

The progress bar threshold is the pourcentage where the metadata filling is considered as suffisant. The progress bar color will change.

It is possible to hide/show the progress bar percentage label.

An option allows to show informative text in the block, it is customizable in Administration or Block settings.

![](https://i.imgur.com/9GoSMPv.png)

#### Metadata tracking
Check the metadata field to track the value.
> The checkboxes cannot be tracked because they have no default value.
> The locked metadata are not tracked by default.

![](https://i.imgur.com/43TTByi.png)

#### Metadata status customization
The block colors are customizable.
![](https://i.imgur.com/7oUHwSg.png)

### Block
If the informative text is not desired, a checkbox disable it. Otherwise custom the content with HTML editor.
![](https://i.imgur.com/vJ5tlwF.png)