def valid_move(color, tube):
    return len(tube) == 0 or color == tube[-1]


def move_from1to2(tube1, tube2, capacity):
    if not tube1:
        return False

    if len(tube2) >= capacity:
        return False

    color_to_move = tube1[-1]
    amount = 0
    for color in reversed(tube1):
        if color == color_to_move:
            amount += 1
        else:
            break

    if not valid_move(color_to_move, tube2):
        return False

    free_space = capacity - len(tube2)
    to_move = min(amount, free_space)

    for _ in range(to_move):
        tube2.append(tube1.pop())

    return True


#def check_tube(tube):
#   tube = list(set(tube))
#   return len(tube) == 1

def is_uniform_tube(tube, capacity):
    if not tube:
        return True

    if len(tube) != capacity:
        return False

    return all(color == tube[0] for color in tube)


def is_game_won(tubes, capacity):
    return all(is_uniform_tube(tube, capacity) for tube in tubes)

