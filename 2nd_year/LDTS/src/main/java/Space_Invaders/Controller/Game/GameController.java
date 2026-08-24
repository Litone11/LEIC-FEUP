package Space_Invaders.Controller.Game;

import Space_Invaders.Controller.Controller;
import Space_Invaders.Model.Game.Arena;
import Space_Invaders.Model.Game.ArenaModifier;

public abstract class GameController extends Controller<Arena> {

    protected final long stepTime = 100;

    private ArenaModifier arenaModifier;
    public GameController(Arena arena){
        super(arena);
        this.arenaModifier = new ArenaModifier(arena);
    }

    public ArenaModifier getArenaModifier() {return arenaModifier;}
}
