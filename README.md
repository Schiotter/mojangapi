# Mojang API

A basic php script combining mojang endpoints together into one

Thats basicaly it, is all in one file as class mojangapi, if using in php you can use the php-array otherwhise, i suggest to use json as format of transport.
The Response contains the following parameters:

* "UUID": String
* "name": String
* "legacy": Bool
* "demo": Bool
* "textures": Array
  * "skin": Null or String [URL]
  * "cape": Null or String [URL]
* "history": Object
  * 0: Array
    * "name": String
    * "time": Null or String
    * "timestamp": Null or Int

If you want to try it yourself, you could obviously use your own Minecraft Name or "iJevin". His Character is perfect for this exampe, because he already change his Name, got a Cape and a custom Skin.
