<?php

declare(strict_types=1);

namespace JavaGen\data;

enum MessageKey: string {

	case COMMAND_LOCATE_INGAME = "commands.locate.ingame";

	case COMMAND_LOCATE_USAGE = "commands.locate.usage";

	case COMMAND_LOCATE_INVALID_CATEGORY = "commands.locate.invalid_category";

	case COMMAND_LOCATE_INVALID_BIOME = "commands.locate.invalid.biome";

	case COMMAND_LOCATE_INVALID_STRUCTURE = "commands.locate.invalid.structure";

	case COMMAND_LOCATE_SUCCESS_BIOME = "commands.locate.success.biome";

	case COMMAND_LOCATE_SUCCESS_STRUCTURE = "commands.locate.success.structure";

	case COMMAND_LOCATE_NOTHING_BIOME = "commands.locate.nothing.biome";

	case COMMAND_LOCATE_NOTHING_STRUCTURE = "commands.locate.nothing.structure";

	case COMMAND_LOCATE_LIST_BIOME = "commands.locate.list.biome";

	case COMMAND_LOCATE_LIST_STRUCTURE = "commands.locate.list.structure";

	case COMMAND_LOCATE_LIST_ELEMENT = "commands.locate.list.element";

	case COMMAND_LOCATE_LIST_SEPARATOR = "commands.locate.list.separator";
}