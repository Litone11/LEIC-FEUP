import json
from pathlib import Path
import tkinter as tk

from rules import is_game_won, move_from1to2

move_count = 0


def load_level(level_name):
    with open(level_name, "r", encoding="utf-8") as f:
        return json.load(f)


def clear_window(janela):
    for widget in janela.winfo_children():
        widget.destroy()


def build_victory_screen(janela, level_name):
    clear_window(janela)

    container = tk.Frame(janela, bg="#eef3f7", padx=24, pady=24)
    container.pack(fill="both", expand=True)

    card = tk.Frame(
        container,
        bg="#ffffff",
        padx=42,
        pady=40,
        highlightthickness=1,
        highlightbackground="#d9e1e7",
    )
    card.place(relx=0.5, rely=0.5, anchor="center")

    badge = tk.Label(
        card,
        text="PUZZLE SOLVED",
        font=("Helvetica", 10, "bold"),
        bg="#dff3e7",
        fg="#27885c",
        padx=12,
        pady=6,
    )
    badge.pack(pady=(0, 16))

    title = tk.Label(
        card,
        text="Victory",
        font=("Helvetica", 30, "bold"),
        bg="#ffffff",
        fg="#22313f",
    )
    title.pack(pady=(0, 10))

    subtitle = tk.Label(
        card,
        text=f"{format_level_title(level_name)} completed",
        font=("Helvetica", 12),
        bg="#ffffff",
        fg="#5f6c7b",
    )
    subtitle.pack(pady=(0, 8))

    moves = tk.Label(
        card,
        text="Nice run.",
        font=("Helvetica", 11),
        bg="#ffffff",
        fg="#7a8793",
    )
    moves.pack(pady=(0, 28))

    button_row = tk.Frame(card, bg="#ffffff")
    button_row.pack(fill="x")

    restart_button = tk.Button(
        button_row,
        text="Restart",
        command=lambda: start_solo_mode(janela, level_name),
        font=("Helvetica", 12, "bold"),
        bg="#27ae60",
        fg="#ffffff",
        activebackground="#229954",
        activeforeground="#ffffff",
        relief="flat",
        padx=24,
        pady=12,
        width=12,
    )
    restart_button.pack(side="left", padx=(0, 10))

    back_button = tk.Button(
        button_row,
        text="Back to Menu",
        command=lambda: show_menu(janela),
        font=("Helvetica", 12, "bold"),
        bg="#34495e",
        fg="#ffffff",
        activebackground="#2c3e50",
        activeforeground="#ffffff",
        relief="flat",
        padx=24,
        pady=12,
        width=12,
    )
    back_button.pack(side="left", padx=(10, 0))


def show_menu(janela):
    from ui.menu import WaterSortMenu

    clear_window(janela)
    WaterSortMenu(janela)


def format_level_title(level_name):
    stem = Path(level_name).stem
    if stem.lower().startswith("level") and stem[5:].isdigit():
        return f"Level {stem[5:]}"
    return stem.replace("_", " ").title()


def draw_tube(canvas, x, y, tube, capacity, tube_index, selected_tube, on_tube_click, tube_width, slot_height):
    tube_height = capacity * slot_height
    outline_color = "#f39c12" if tube_index == selected_tube else "#34495e"
    outline_width = max(4, slot_height // 6) if tube_index == selected_tube else max(3, slot_height // 9)
    inner_padding = max(4, tube_width // 12)
    click_padding = max(8, tube_width // 8)
    tag = f"tube_{tube_index}"
    color_map = {
        "red": "#e74c3c",
        "blue": "#3498db",
        "green": "#2ecc71",
        "yellow": "#f1c40f",
        "purple": "#8e44ad",
        "orange": "#e67e22",
        "pink": "#fd79a8",
        "cyan": "#00cec9",
    }

    canvas.create_rectangle(
        x - click_padding,
        y - click_padding,
        x + tube_width + click_padding,
        y + tube_height + click_padding,
        outline="",
        fill="",
        tags=tag,
    )
    canvas.create_line(
        x,
        y,
        x,
        y + tube_height,
        width=outline_width,
        fill=outline_color,
        tags=tag,
    )
    canvas.create_line(
        x + tube_width,
        y,
        x + tube_width,
        y + tube_height,
        width=outline_width,
        fill=outline_color,
        tags=tag,
    )
    canvas.create_line(
        x,
        y + tube_height,
        x + tube_width,
        y + tube_height,
        width=outline_width,
        fill=outline_color,
        tags=tag,
    )

    for index, color in enumerate(tube):
        block_y1 = y + tube_height - ((index + 1) * slot_height)
        block_y2 = y + tube_height - (index * slot_height)
        canvas.create_rectangle(
            x + inner_padding,
            block_y1 + inner_padding,
            x + tube_width - inner_padding,
            block_y2 - inner_padding,
            fill=color_map.get(color, "#95a5a6"),
            outline="",
            tags=tag,
        )

    canvas.tag_bind(tag, "<Button-1>", lambda _event: on_tube_click(tube_index))


def build_solo_screen(janela, state, level_name, selected_tube, on_tube_click):
    clear_window(janela)
    janela.update_idletasks()

    container = tk.Frame(janela, bg="#f3f5f7", padx=24, pady=24)
    container.pack(fill="both", expand=True)

    top_bar = tk.Frame(container, bg="#f3f5f7")
    top_bar.pack(fill="x", pady=(0, 18))
    top_bar.grid_columnconfigure(1, weight=1)

    back_button = tk.Button(
        top_bar,
        text="Back to Menu",
        command=lambda: show_menu(janela),
        font=("Helvetica", 10, "bold"),
        bg="#dde4ea",
        fg="#22313f",
        activebackground="#cfd8df",
        activeforeground="#22313f",
        relief="flat",
        bd=0,
        padx=16,
        pady=10,
        cursor="hand2",
    )
    back_button.grid(row=0, column=0, sticky="w")

    title_card = tk.Frame(top_bar, bg="#ffffff", padx=22, pady=14, highlightthickness=1, highlightbackground="#d9e1e7")
    title_card.grid(row=0, column=1)

    header = tk.Label(
        title_card,
        text=format_level_title(level_name),
        font=("Helvetica", 18, "bold"),
        bg="#ffffff",
        fg="#22313f",
    )
    header.pack()

    score_card = tk.Frame(top_bar, bg="#ffffff", padx=18, pady=12, highlightthickness=1, highlightbackground="#d9e1e7")
    score_card.grid(row=0, column=2, sticky="e")

    score_label = tk.Label(
        score_card,
        text="Move Count",
        font=("Helvetica", 9, "bold"),
        bg="#ffffff",
        fg="#7a8793",
    )
    score_label.pack()

    score = tk.Label(
        score_card,
        text=str(state["move_count"]),
        font=("Helvetica", 18, "bold"),
        bg="#ffffff",
        fg="#22313f",
    )
    score.pack()

    board_card = tk.Frame(container, bg="#ffffff", padx=12, pady=12, highlightthickness=1, highlightbackground="#d9e1e7")
    board_card.pack(fill="both", expand=True)

    canvas = tk.Canvas(
        board_card,
        bg="#ffffff",
        highlightthickness=0,
        width=760,
        height=460,
    )
    canvas.pack(fill="both", expand=True)

    top_margin = 36
    canvas_width = max(board_card.winfo_width() - 24, janela.winfo_width() - 96, 420)
    canvas_height = max(board_card.winfo_height() - 24, janela.winfo_height() - 220, 320)
    layout = compute_tube_layout(len(state["tubes"]), state["capacity"], canvas_width, canvas_height)
    tube_width = layout["tube_width"]
    slot_height = layout["slot_height"]
    tube_height = state["capacity"] * slot_height
    spacing = layout["column_gap"]
    row_gap = layout["row_gap"]
    label_gap = layout["label_gap"]
    max_per_row = layout["columns"]
    rows = [
        state["tubes"][index:index + max_per_row]
        for index in range(0, len(state["tubes"]), max_per_row)
    ]

    tube_index = 0
    for row_index, row_tubes in enumerate(rows):
        row_width = (len(row_tubes) * tube_width) + ((len(row_tubes) - 1) * spacing)
        start_x = max(24, (canvas_width - row_width) / 2)
        y = top_margin + row_index * (tube_height + label_gap + row_gap)

        for column_index, tube in enumerate(row_tubes):
            x = start_x + (column_index * (tube_width + spacing))
            draw_tube(
                canvas,
                x,
                y,
                tube,
                state["capacity"],
                tube_index,
                selected_tube,
                on_tube_click,
                tube_width,
                slot_height,
            )
            canvas.create_text(
                x + (tube_width / 2),
                y + tube_height + label_gap,
                text=f"Tubo {tube_index + 1}",
                font=("Helvetica", layout["label_font"], "bold"),
                fill="#34495e",
            )
            tube_index += 1

    total_height = top_margin + (len(rows) * (tube_height + label_gap + 16)) + (max(0, len(rows) - 1) * row_gap) + 24
    canvas.configure(scrollregion=(0, 0, canvas_width, total_height))


def compute_tube_layout(total_tubes, capacity, canvas_width, canvas_height):
    best = None
    side_margin = 48
    min_gap = 18

    for columns in range(total_tubes, 0, -1):
        rows = ((total_tubes - 1) // columns) + 1
        usable_width = canvas_width - (side_margin * 2)
        usable_height = canvas_height - 70 - ((rows - 1) * 34)

        if usable_width <= 0 or usable_height <= 0:
            continue

        candidate_width = (usable_width - ((columns - 1) * min_gap)) // columns
        slot_height = min(int(candidate_width * 0.62), usable_height // (rows * capacity), 42)
        if slot_height < 18:
            continue

        tube_width = min(candidate_width, int(slot_height * 1.68), 70)
        column_gap = 0 if columns == 1 else max(min_gap, (usable_width - (columns * tube_width)) // (columns - 1))
        row_gap = 54 if rows == 1 else max(32, min(70, (canvas_height - 50 - (rows * ((capacity * slot_height) + 38))) // (rows - 1)))

        candidate = {
            "columns": columns,
            "tube_width": tube_width,
            "slot_height": slot_height,
            "column_gap": column_gap,
            "row_gap": row_gap,
            "label_gap": max(22, min(30, slot_height // 2 + 8)),
            "label_font": max(9, min(11, slot_height // 4 + 1)),
            "score": tube_width * slot_height,
        }

        if best is None or candidate["score"] > best["score"]:
            best = candidate

    if best is None:
        return {
            "columns": min(total_tubes, 4),
            "tube_width": 36,
            "slot_height": 20,
            "column_gap": 18,
            "row_gap": 34,
            "label_gap": 22,
            "label_font": 9,
        }

    del best["score"]
    return best


def start_solo_mode(janela, level_name):
    data = load_level(level_name)
    state = {
        "capacity": data["capacity"],
        "tubes": data["tubes"],
        "won": False,
        "move_count": 0,
    }
    selected_tube = None

    def render():
        build_solo_screen(
            janela,
            state,
            level_name,
            selected_tube,
            on_tube_click,
        )

    def on_tube_click(index):
        nonlocal selected_tube

        if state["won"]:
            return

        if selected_tube is None:
            if not state["tubes"][index]:
                return
            selected_tube = index
            render()
            return

        if selected_tube == index:
            selected_tube = None
            render()
            return

        moved = play_move(state, selected_tube, index)

        selected_tube = None

        if moved:
            state["move_count"] += 1

            if is_game_won(state["tubes"], state["capacity"]):
                state["won"] = True
                build_victory_screen(janela, level_name)
                return

        render()

    render()


def play_move(state, from_index, to_index):
    return move_from1to2(
        state["tubes"][from_index],
        state["tubes"][to_index],
        state["capacity"],
    )
