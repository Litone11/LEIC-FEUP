import json
from pathlib import Path
import tkinter as tk

from modes.solo import start_solo_mode


class WaterSortMenu:
    def __init__(self, janela):
        self.janela = janela
        self.level_selecionado = tk.StringVar(master=self.janela)
        self.level_cards = {}
        self.levels = self.carregar_opcoes_levels()

        if self.levels:
            self.level_selecionado.set(self.levels[0]["path"])

        self._build_main_menu()

    def _clear_window(self):
        for widget in self.janela.winfo_children():
            widget.destroy()

    def _build_main_menu(self):
        self._clear_window()

        contentor = tk.Frame(self.janela, bg="#f6f1e8", padx=32, pady=28)
        contentor.pack(fill="both", expand=True)

        titulo = tk.Label(
            contentor,
            text="Water Sort",
            font=("Georgia", 30, "bold"),
            bg="#f6f1e8",
            fg="#203040",
        )
        titulo.pack(pady=(36, 8))

        subtitulo = tk.Label(
            contentor,
            text="Sort every color into its own tube",
            font=("Helvetica", 12),
            bg="#f6f1e8",
            fg="#6a7682",
        )
        subtitulo.pack(pady=(0, 32))

        card = tk.Frame(contentor, bg="#ffffff", padx=28, pady=28)
        card.pack(pady=(0, 24))

        botao_solo = tk.Button(
            card,
            text="Play Solo",
            command=self._start_selected_level,
            font=("Helvetica", 14, "bold"),
            bg="#efb05b",
            fg="#203040",
            activebackground="#e2a14a",
            activeforeground="#203040",
            relief="flat",
            bd=0,
            padx=28,
            pady=14,
            width=18,
            cursor="hand2",
        )
        botao_solo.pack(pady=(0, 14))

        botao_levels = tk.Button(
            card,
            text="Choose Level",
            command=self._build_level_selection_screen,
            font=("Helvetica", 14, "bold"),
            bg="#dfe5ea",
            fg="#203040",
            activebackground="#d3dbe2",
            activeforeground="#203040",
            relief="flat",
            bd=0,
            padx=28,
            pady=14,
            width=18,
            cursor="hand2",
        )
        botao_levels.pack(pady=14)

        botao_ai = tk.Button(
            card,
            text="Play AI",
            command=lambda: None,
            font=("Helvetica", 14, "bold"),
            bg="#dfe5ea",
            fg="#203040",
            activebackground="#d3dbe2",
            activeforeground="#203040",
            relief="flat",
            bd=0,
            padx=28,
            pady=14,
            width=18,
            cursor="hand2",
        )
        botao_ai.pack(pady=(14, 0))

        if not self.levels:
            botao_solo.configure(state="disabled", bg="#c8d0d8", cursor="")
            botao_levels.configure(state="disabled", bg="#c8d0d8", cursor="")
            aviso = tk.Label(
                contentor,
                text="No levels found",
                font=("Helvetica", 11),
                bg="#f6f1e8",
                fg="#7c8792",
            )
            aviso.pack()

    def _build_level_selection_screen(self):
        self._clear_window()
        self.level_cards = {}

        contentor = tk.Frame(self.janela, bg="#f6f1e8", padx=32, pady=28)
        contentor.pack(fill="both", expand=True)

        topo = tk.Frame(contentor, bg="#f6f1e8")
        topo.pack(fill="x", pady=(0, 20))
        topo.grid_columnconfigure(1, weight=1)

        voltar = tk.Button(
            topo,
            text="Back",
            command=self._build_main_menu,
            font=("Helvetica", 11, "bold"),
            bg="#dfe5ea",
            fg="#203040",
            activebackground="#d3dbe2",
            activeforeground="#203040",
            relief="flat",
            bd=0,
            padx=18,
            pady=10,
            cursor="hand2",
        )
        voltar.grid(row=0, column=0, sticky="w")

        titulo = tk.Label(
            topo,
            text="Choose Level",
            font=("Georgia", 24, "bold"),
            bg="#f6f1e8",
            fg="#203040",
        )
        titulo.grid(row=0, column=1)

        filler = tk.Frame(topo, bg="#f6f1e8", width=72)
        filler.grid(row=0, column=2)

        painel = tk.Frame(contentor, bg="#ffffff", padx=22, pady=22)
        painel.pack(fill="both", expand=True)

        self._build_level_grid(painel)
        self._build_selection_actions(painel)
        self._refresh_selection()

    def _build_level_grid(self, parent):
        grid_wrap = tk.Frame(parent, bg="#ffffff")
        grid_wrap.pack(fill="both", expand=True)

        level_grid = tk.Frame(grid_wrap, bg="#ffffff")
        level_grid.pack(fill="both", expand=True)

        if not self.levels:
            vazio = tk.Label(
                level_grid,
                text="No levels found",
                font=("Helvetica", 12),
                bg="#ffffff",
                fg="#7c8792",
                pady=30,
            )
            vazio.pack()
            return

        for column in range(3):
            level_grid.grid_columnconfigure(column, weight=1)

        for index, level in enumerate(self.levels):
            row = index // 3
            column = index % 3
            self._build_level_card(level_grid, level, row, column)

    def _build_level_card(self, parent, level, row, column):
        path = level["path"]
        card = tk.Frame(
            parent,
            bg="#f9f6f0",
            padx=14,
            pady=14,
            cursor="hand2",
            highlightthickness=2,
            highlightbackground="#ebe3d6",
            highlightcolor="#ebe3d6",
        )
        card.grid(row=row, column=column, sticky="nsew", padx=8, pady=8)

        titulo = tk.Label(
            card,
            text=level["display_name"],
            font=("Helvetica", 13, "bold"),
            bg="#f9f6f0",
            fg="#203040",
            cursor="hand2",
        )
        titulo.pack(anchor="w")

        amostra = tk.Frame(card, bg="#f9f6f0")
        amostra.pack(anchor="w", pady=(10, 6))

        for color in level["palette"]:
            bolha = tk.Canvas(
                amostra,
                width=14,
                height=14,
                bg="#f9f6f0",
                highlightthickness=0,
                bd=0,
            )
            bolha.pack(side="left", padx=(0, 6))
            bolha.create_oval(1, 1, 13, 13, fill=self._map_color(color), outline="")

        for widget in (card, titulo, amostra):
            widget.bind("<Button-1>", lambda _event, p=path: self._set_level(p))

        self.level_cards[path] = {
            "frame": card,
            "title": titulo,
            "sample": amostra,
        }

    def _build_selection_actions(self, parent):
        acoes = tk.Frame(parent, bg="#ffffff")
        acoes.pack(fill="x", pady=(18, 0))
        acoes.grid_columnconfigure(0, weight=1)
        acoes.grid_columnconfigure(1, weight=1)

        self.botao_confirmar = tk.Button(
            acoes,
            text="Play Solo",
            command=self._start_selected_level,
            font=("Helvetica", 13, "bold"),
            bg="#efb05b",
            fg="#203040",
            activebackground="#e2a14a",
            activeforeground="#203040",
            relief="flat",
            bd=0,
            padx=18,
            pady=14,
            cursor="hand2",
        )
        self.botao_confirmar.grid(row=0, column=0, sticky="ew", padx=(0, 8))

        cancelar = tk.Button(
            acoes,
            text="Back",
            command=self._build_main_menu,
            font=("Helvetica", 13, "bold"),
            bg="#dfe5ea",
            fg="#203040",
            activebackground="#d3dbe2",
            activeforeground="#203040",
            relief="flat",
            bd=0,
            padx=18,
            pady=14,
            cursor="hand2",
        )
        cancelar.grid(row=0, column=1, sticky="ew", padx=(8, 0))

    def _set_level(self, level_path):
        self.level_selecionado.set(level_path)
        self._refresh_selection()

    def _refresh_selection(self):
        selecionado = self.level_selecionado.get()

        for path, widgets in self.level_cards.items():
            ativo = path == selecionado
            bg = "#fff0d4" if ativo else "#f9f6f0"
            border = "#ebb25a" if ativo else "#ebe3d6"
            widgets["frame"].configure(
                bg=bg,
                highlightbackground=border,
                highlightcolor=border,
            )
            widgets["title"].configure(bg=bg)
            widgets["sample"].configure(bg=bg)

        if hasattr(self, "botao_confirmar"):
            self.botao_confirmar.configure(
                state="normal" if selecionado else "disabled",
                bg="#efb05b" if selecionado else "#c8d0d8",
                cursor="hand2" if selecionado else "",
            )

    def _start_selected_level(self):
        selecionado = self.level_selecionado.get()
        if selecionado:
            start_solo_mode(self.janela, selecionado)

    def carregar_opcoes_levels(self):
        base_dir = Path(__file__).resolve().parent.parent
        ficheiros_levels = sorted(
            path.relative_to(base_dir).as_posix()
            for path in base_dir.rglob("levels/*.json")
            if path.is_file() and "__pycache__" not in path.parts
        )

        levels = []
        for level_path in ficheiros_levels:
            full_path = base_dir / level_path
            with open(full_path, "r", encoding="utf-8") as ficheiro:
                data = json.load(ficheiro)

            palette = sorted({color for tube in data.get("tubes", []) for color in tube})
            levels.append(
                {
                    "path": level_path,
                    "display_name": self._format_level_name(full_path.stem),
                    "palette": palette[:5],
                }
            )

        return levels

    def _format_level_name(self, stem):
        if stem.lower().startswith("level") and stem[5:].isdigit():
            return f"Level {stem[5:]}"
        return stem.replace("_", " ").title()

    def _map_color(self, color):
        return {
            "red": "#e74c3c",
            "blue": "#3498db",
            "green": "#2ecc71",
            "yellow": "#f1c40f",
            "purple": "#8e44ad",
            "orange": "#e67e22",
            "pink": "#fd79a8",
            "cyan": "#00cec9",
        }.get(color, "#95a5a6")
