# QuizAccess - Sebprogram #

## Introduction

This Moodle plugin extends the functionality of Safe Exam Browser (SEB), allowing administrators/teachers to specify certain programs that will be permitted during quizzes. With this tool, administrators/teachers can enhance control over the SEB, ensuring that only authorized applications are accessible to students while completing their assessments.All of these settings can be managed directly from Moodle, within the quiz configuration.

[<img src="https://unimoodle.github.io/assets/images/unimoodle-primarylogo-rgb-1200x353.png" height="70px"/>](https://unimoodle.github.io)


## System Requirements ##
Compatible with Moodle 4.1 and newer versions.


## Installing manually ##


The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/quiz/accessrule/sebprogram

Afterwards, log in to your Moodle site as an admin and go to _Site administration to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2023 ISYC

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
