# roller
🎲 WordPress plugin for dice rolling, random lists, and conditional variables

[![Build status](https://api.travis-ci.org/scotchfield/roller.svg?branch=master)](https://travis-ci.org/scotchfield/roller)

## Roll some dice

You write:

		STR: [roller 3d6]
		DEX: [roller 3d6]
		INT: [roller 2d6+6]

The page shows:

> STR: 7
> DEX: 11
> INT: 17

## Save dice rolls as variables

You write:

		[roller 3d6 var=str][roller 3d6 var=dex][roller 2d6+6 var=int]

		STR: [roller_var str]
		DEX: [roller_var dex]
		INT: [roller_var int]

The page shows:

> STR: 12
> DEX: 7
> INT: 13
