import tkinter as tk

from ui.menu import WaterSortMenu


def main():
    # Cria e configura a janela principal da aplicação.
    janela = tk.Tk()
    janela.title("Water Sort")
    janela.geometry("860x620")
    janela.minsize(640, 460)
    janela.configure(bg="#f6f1e8")

    # Monta o menu inicial dentro da janela principal.
    WaterSortMenu(janela)

    # Inicia o ciclo de eventos do Tkinter.
    janela.mainloop()


if __name__ == "__main__":
    main()
