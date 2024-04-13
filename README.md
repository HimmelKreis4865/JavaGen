# <img src="icon.png" alt="icon" width="28"/> ‎ ‎  JavaGen
JavaGen is a PocketMine-MP plugin that provides a fast way to generate the Minecraft **Vanilla terrain** (all dimensions) based on a Java Server

The speed of the generator may be influenced by the available hardware power, and further optimizations will follow in the near future.

## Installation
You need
- [PocketMine-MP API-5](https://github.com/pmmp/PocketMine-MP/releases/latest)
- Java 17
- a java server, preferably a spigot server (everything was tested on a [Paperspigot](https://papermc.io/downloads/paper) server, version 1.20.4))
- [ChunkSender](https://github.com/HimmelKreis4865/ChunkSender/releases/latest) the Java-Plugin, required to use the generator
- There are 3 new generators you can use to generate the vanilla world:
    - overworld: `java_overworld`
    - nether: `java_nether`
    - end: `java_end`

## Ingame footage
- ### A beautiful landscape in the jungle
![Landscape](https://i.imgur.com/RBkyyI0.png)

- ### A deep look in the underground
![AncientCity](https://i.imgur.com/yNS9MiQ.png)

- ### A landscape in the nether
![Landscape Nether](https://i.imgur.com/PZjCioP.png)

- ### A very scary place in the nether
![Bastion](https://i.imgur.com/GcyA7TK.png)

- ### "The City at the End of the Game"
![End City](https://i.imgur.com/TBOnOhU.png)

## Features
- Full generation of the vanilla terrain in all dimensions (overworld, nether and the end)
- Generation of vanilla-like chest loot
- /locate command to locate biomes and structures close by
- An API for interaction with structures
- Fully configurable messages (for the locate command)

## Known issues
- Missing blocks and items in PocketMine-MP will NOT be generated in the world
- Weird sky color in nether and end dimension
- Java server must be on the same machine as the pocketmine server, port cannot be changed yet

## Commands
### Locate
#### Usages:
 - `/locate list <structure|biome>`   Lists all locatable structures or biomes
 - `/locate structure <structure>`   Locates the nearest structure of a specific type, for a list of all available structures, see `/locate list`
 - `/locate biome <biome>`   Locates the nearest biome of a specific type, for a list of all available biomes, see `/locate list`

#### Permission: 
`locate.command`

## Any problems or ideas?
Feel free to open an issue on github