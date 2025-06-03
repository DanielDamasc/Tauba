import sys
import json
import numpy as np
import os

# Define o diretório onde o matplotlib vai guardar as configs temporárias.
os.environ["MPLCONFIGDIR"] = "C:/laragon/www/Tauba/temp_matplotlib_config"

import matplotlib.pyplot as plt

# Recebe o JSON passado pelo Laravel.
restricoesJSON = sys.argv[1]
restricoes = json.loads(restricoesJSON)

# Definindo o intervalo para x.
x = np.linspace(0, 50, 500)

# Criar uma figura.
plt.figure(figsize=(8, 6))

# Plotar cada restrição.
for restricao in restricoes:
    coef = restricao['coeficientes']
    sinal = restricao['sinal']
    termo = float(restricao['termo'])

    a = float(coef["1"])
    b = float(coef["2"])

    # Evitar divisão por zero.
    if b != 0:
        y = (termo - a * x) / b
    else:
        y = np.full_like(x, np.nan)
        y[x == (termo / a)] = 0

    # Plot da reta.
    label = f"{a}x + {b}y {sinal} {termo}"
    plt.plot(x, y, label=label)

    # Preencher a região.
    if sinal == "<=":
        plt.fill_between(x, 0, y, where=(y >= 0), alpha=0.2)
    elif sinal == ">=":
        plt.fill_between(x, y, 50, where=(y >= 0), alpha=0.2)

# Configurações.
plt.xlim(0, 10)
plt.ylim(0, 10)
plt.xlabel('x')
plt.ylabel('y')
plt.title('Região de Viabilidade - Simplex')
plt.grid(True)
plt.legend()

# Salvar o gráfico em arquivo.
caminho_saida = '../public/graficos/solucao.png'
plt.savefig(caminho_saida)

# Retorno pro Laravel.
print(json.dumps({"caminho": caminho_saida}))